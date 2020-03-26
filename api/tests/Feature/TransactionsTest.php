<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Transaction;
use App\Account;

class TransactionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_transaction_can_be_added_and_account_balanced_are_updated() {

        $accounts = factory(Account::class, 2)->create();
        $expectedBalanceFrom = $accounts[0]->balance - 300;
        $expectedBalanceTo = $accounts[1]->balance + 300;
        $response = $this->post('/api/accounts/' . $accounts[0]->id . '/transactions', $this->data($accounts[0]->id, $accounts[1]->id));
        $response->assertSessionHasNoErrors();
        $this->assertCount(1, Transaction::all());

        $transaction = Transaction::first();

        $this->assertEquals($accounts[0]->id, $transaction->from);
        $this->assertEquals($accounts[1]->id, $transaction->to);
        $this->assertEquals(300, $transaction->amount);
        $this->assertEquals('test details', $transaction->details);

        $this->assertEquals($expectedBalanceFrom, $accounts[0]->refresh()->balance);
        $this->assertEquals($expectedBalanceTo, $accounts[1]->refresh()->balance);
    }

    /** @test */
    public function amount_details_are_required() {
        $accounts = factory(Account::class, 2)->create();
        $response = $this->post('/api/accounts/' . $accounts[1]->id . '/transactions', [
            'to' => $accounts[0]->id,
            'from' => $accounts[1]->id
        ])->decodeResponseJson();

        $this->assertEquals($response['amount'][0], "The amount field is required.");
        $this->assertEquals($response['details'][0], "The details field is required.");
        $this->assertCount(0, Transaction::all());
    }

    /** @test */
    public function account_cant_send_amount_to_itself() {
        $account = factory(Account::class)->create();
        $response = $this->post('/api/accounts/' . $account->id . '/transactions', $this->data($account->id, $account->id));
        $response->assertStatus(401);
        $this->assertCount(0, Transaction::all());
    }

    /** @test */
    public function account_cant_send_amount_superior_to_balance() {
        $accounts = factory(Account::class, 2)->create();
        $response = $this->post('/api/accounts/' . $accounts[0]->id . '/transactions', array_merge(
            $this->data($accounts[0]->id, $accounts[1]->id),
            [
                'amount' => $accounts[0]->balance + 10000
            ]
        ));
        $response->assertStatus(400);
        $this->assertCount(0, Transaction::all());
    }

    /** @test */
    public function account_cant_send_amount_from_another_account() {
        $accounts = factory(Account::class, 2)->create();
        $response = $this->post('/api/accounts/' . $accounts[1]->id . '/transactions', $this->data($accounts[0]->id, $accounts[1]->id));
        $response->assertStatus(401);
        $this->assertCount(0, Transaction::all());
    }

    private function data($from, $to) {
        return [
            'from' => $from,
            'to' => $to,
            'amount' => 300,
            'details' => 'test details'
        ];
    }
}
