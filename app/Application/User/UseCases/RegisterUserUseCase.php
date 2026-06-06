<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\RegisterUserDTO;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Resend\Laravel\Facades\Resend;

class RegisterUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function execute(RegisterUserDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $activationToken = Str::random(64);

            $user = $this->repository->create([
                'name' => $dto->name,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'password' => Hash::make('sakupos123'),
                'is_active' => false,
                'activation_token' => $activationToken,
                'activation_token_expires_at' => now()->addDay(),
            ]);

            $user->assignRole($dto->role);

            $activationUrl = config('app.frontend_url')
                . "/activate-account?token={$activationToken}";

            Resend::emails()->send([
                'from' => config('mail.from.name') . ' <' . config('mail.from.address') . '>',
                'to' => [$dto->email],
                'subject' => 'Aktivasi Akun Saku POS',
                'html' => "
                    <p>Halo {$dto->name},</p>
                    <p>Akun Saku POS kamu sudah dibuat sebagai <strong>{$dto->role}</strong>.</p>
                    <p>Silakan klik link berikut untuk mengaktifkan akun:</p>
                    <p><a href='{$activationUrl}'>Aktifkan Akun</a></p>
                    <p>Password awal kamu adalah: <strong>sakupos123</strong></p>
                    <p>Silahkan ganti password setelah pertama kali login.</p>
                    <p>Link ini berlaku selama 24 jam.</p>
                ",
            ]);
        });
    }
}