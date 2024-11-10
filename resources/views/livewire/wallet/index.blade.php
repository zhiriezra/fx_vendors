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

    <div class="card">
      <div class="card-body">

        <div class="text-lg">
          <strong>Transaction History</strong>
        </div>
        <div class="card-body table-border-style">
          <div class="datatable-top">
            
            <button wire:click="exportTransactions" class="btn btn-light-primary mb-1 btn-sm csv">
                <span wire:loading.remove wire:target="exportTransactions">Export</span>
                <span wire:loading wire:target="exportTransactions">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Exporting...
                </span>
            </button>
    </div>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Transaction ID</th>
                  <th>Amount</th>
                  <th>Type</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($transactions as $transaction)
                <tr wire:key="{{$transaction->id }} ">
                  <td>{{$loop->iteration }}</td>
                  <td>{{$transaction->uuid }}</td>
                  <td>&#8358;{{ number_format($transaction->amount,2) }}</td>
                  <td>{{$transaction->type }}</td>
                  <td>{{$transaction->created_at->toFormattedDateString() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6"><h5 style="text-align: center;">0 Transactions</h5></td>
                </tr>
                @endforelse 
              </tbody>
            </table>
          </div>
        </div>
        
      </div>

      
  </div>
</div>
