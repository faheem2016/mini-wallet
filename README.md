# Mini Wallet — Laravel + Vue 3

## Overview
This project implements a simplified digital wallet with:
- Laravel API backend (users + transactions)
- Safe, concurrent transfers using DB transactions + row locks
- Commission (1.5%) charged to sender
- Broadcast transaction events via Pusher (private `user.{id}` channels)
- Vue 3 frontend that listens to Pusher events (via Echo) and updates UI in real time

## Quick Setup (local)
### Backend
1. `cd project-directory`
2. `cp .env.example .env` and fill DB & Pusher details.
3. `composer install`
4. `php artisan key:generate`
5. Create database `mini_wallet` (or match `.env`)
6. `php artisan migrate --seed`
7. Setup broadcasting keys in `.env` (PUSHER_APP_ID, PUSHER_APP_KEY, PUSHER_APP_SECRET)
8. `php artisan serve` (default http://127.0.0.1:8000)

### Frontend
1. `npm install`
4. `npm run dev` (or `vite`/`serve` as configured)

## Authentication
Laravel sanctum is being used.

