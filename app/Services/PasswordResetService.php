<?php

namespace App\Services;

use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Interfaces\Services\PasswordResetServiceInterface;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService implements PasswordResetServiceInterface
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function sendResetLink(string $email): bool
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            return false;
        }

        $token = Str::random(64);
        $this->userRepository->updateResetToken($user->id, $token);

        Mail::to($user->email)->send(new PasswordResetMail($token));

        return true;
    }

    public function resetPassword(string $token, string $password): bool
    {
        $user = $this->userRepository->findByResetToken($token);
        
        if (!$user) {
            return false;
        }

        return $this->userRepository->updatePassword($user->id, $password);
    }

    public function validateToken(string $token): bool
    {
        return $this->userRepository->findByResetToken($token) !== null;
    }
} 