<?php

namespace App\Http\Controllers\Api;

use App\Events\TransactionCreated;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    // GET /api/transactions
    public function index(Request $request)
    {
        $user = $request->user();

        // Paginated results (incoming and outgoing)
        $transactions = Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json([
            'balance' => $user->balance,
            'transactions' => $transactions
        ]);
    }

    // POST /api/transactions
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $senderId = $request->user()->id;
        $receiverId = (int) $request->input('receiver_id');
        $amount = round((float) $request->input('amount'), 4);

        if ($senderId === $receiverId) {
            throw ValidationException::withMessages(['receiver_id' => 'You cannot send money to yourself.']);
        }

        // Commission 1.5%
        $commissionRate = 0.015;
        $commission = round($amount * $commissionRate, 4);
        $totalDebit = round($amount + $commission, 4);

        // Atomic operation with pessimistic locking
        $transaction = DB::transaction(function () use ($senderId, $receiverId, $amount, $commission, $totalDebit) {

            // Lock both rows in deterministic order to avoid deadlocks
            $first = min($senderId, $receiverId);
            $second = max($senderId, $receiverId);

            $firstUser = User::where('id', $first)->lockForUpdate()->first();
            $secondUser = User::where('id', $second)->lockForUpdate()->first();

            // Re-fetch sender and receiver from the locked set:
            $sender = $senderId === $first ? $firstUser : $secondUser;
            $receiver = $receiverId === $first ? $firstUser : $secondUser;

            if (!$sender || !$receiver) {
                throw new \Exception('Sender or receiver not found.');
            }

            // Ensure sufficient balance
            if (bccomp($sender->balance, $totalDebit, 4) < 0) {
                throw ValidationException::withMessages(['amount' => 'Insufficient balance.']);
            }

            // Update balances
            $sender->balance = bcsub($sender->balance, $totalDebit, 4);
            $sender->save();

            $receiver->balance = bcadd($receiver->balance, $amount, 4);
            $receiver->save();

            // Create transaction record
            $tx = Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'commission_fee' => $commission,
                'total_debit' => $totalDebit,
                'status' => 'completed',
            ]);

            // Broadcast event (sender & receiver will be notified)
            TransactionCreated::dispatch($tx);

            return $tx;
        });

        return response()->json([
            'message' => 'Transfer successful',
            'transaction' => $transaction
        ], Response::HTTP_CREATED);
    }
}
