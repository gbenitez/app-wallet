<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    private function transfer(int $fromId, int $toId, string $amount)
    {
        return $this->postJson('/api/transfers', [
            'from_account_id' => $fromId,
            'to_account_id' => $toId,
            'amount' => $amount,
        ]);
    }

    public function test_transfiera_y_actualiza_saldos(): void
    {
        $from = Account::factory()->create(['balance' => 1000000]);
        $to = Account::factory()->create(['balance' => 0]);

        $this->transfer($from->id, $to->id, '25000')
            ->assertCreated()
            ->assertJsonPath('data.status', 'completed');

        $this->assertEquals(975000, $from->refresh()->balance);
        $this->assertEquals(25000, $to->refresh()->balance);
    }

    public function test_rechaza_saldo_insuficiente_sin_tocar_saldos(): void
    {
        $from = Account::factory()->create(['balance' => 1000]);
        $to = Account::factory()->create(['balance' => 0]);

        $this->transfer($from->id, $to->id, '5000')
            ->assertStatus(422)
            ->assertJsonPath('message', 'saldo insuficiente');

        $this->assertEquals('1000.00', $from->refresh()->balance);
        $this->assertEquals('0.00', $to->refresh()->balance);
        $this->assertDatabaseHas('transfers', [
            'from_account_id' => $from->id,
            'status' => 'failed',
        ]);
    }

    public function test_rechaza_transferir_a_la_misma_cuenta(): void
    {
        $account = Account::factory()->create();

        $this->transfer($account->id, $account->id, '100')
            ->assertStatus(422);
    }

    public function test_rechaza_cuenta_bloqueada(): void
    {
        $from = Account::factory()->create();
        $to = Account::factory()->create(['status' => 'blocked']);

        $this->transfer($from->id, $to->id, '100')
            ->assertStatus(422);
        $this->assertEquals('1000000.00', $from->refresh()->balance);

    }

    public function test_valida_el_monto(): void
    {
        $from = Account::factory()->create();
        $to = Account::factory()->create();
        $this->transfer($from->id, $to->id, -50)
            ->assertStatus(422)
            ->assertJsonValidationErrors('amount');
    }
}
