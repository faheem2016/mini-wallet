<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        // include relationships if needed
        $this->transaction = $transaction->load('sender', 'receiver');
    }

    public function broadcastOn()
    {
        $senderChannel = new PrivateChannel('user.' . $this->transaction->sender_id);
        $receiverChannel = new PrivateChannel('user.' . $this->transaction->receiver_id);

        // For convenience, broadcast a separate payload to both channels
        // Laravel will dispatch event to both channels
        return [$senderChannel, $receiverChannel];
    }

    public function broadcastWith()
    {
        return [
            'transaction' => $this->transaction->toArray(),
        ];
    }

    public function broadcastAs()
    {
        return 'transaction.created';
    }
}
