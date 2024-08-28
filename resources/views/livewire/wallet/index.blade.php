<div>



    <div class="card">
        <div class="card-body">

            <div class="text-lg">
                Wallet balance: &#8358;{{ number_format($balance,2)  }}
            </div>
          <div class="input-group my-2">
            <input type="number" wire:model="amount" class="form-control" placeholder="Amount to withdraw">
            <button class="btn btn-outline-secondary" type="button" wire:click="requestWithdraw">Request Withdrawal</button>
          </div>
        </div>
    </div>
</div>
