<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    /**
     * Determine whether the user can view any loans.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isHr();
    }

    /**
     * Determine whether the user can view the loan.
     */
    public function view(User $user, Loan $loan): bool
    {
        return $user->isAdmin() || $user->isHr() || $loan->user_id === $user->id;
    }

    /**
     * Determine whether the user can create loans.
     */
    public function create(User $user): bool
    {
        return true; // All users can apply for loans
    }

    /**
     * Determine whether the user can update the loan.
     */
    public function update(User $user, Loan $loan): bool
    {
        return $user->isAdmin() || $user->isHr();
    }

    /**
     * Determine whether the user can approve the loan.
     */
    public function approve(User $user, Loan $loan): bool
    {
        return $user->isAdmin() || $user->isHr();
    }

    /**
     * Determine whether the user can delete the loan.
     */
    public function delete(User $user, Loan $loan): bool
    {
        return $user->isAdmin();
    }
}
