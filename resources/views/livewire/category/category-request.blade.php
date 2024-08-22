<div class="row">
    @if(Session::has('message'))
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle px-3"></i>
            <div> {{ Session::get('message') }}</div>
        </div>     
    @endif
    <!-- [ form-element ] start -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5>Category Requests</h5>
          <hr>
          <form wire:submit.prevent="saveCategory">
            <div class="row g-4">
              <div class="col-md-12">
                <div class="form-floating mb-4">
                  <input type="text" class="form-control" id="floatingInput" placeholder="Category Name" wire:model="name">
                  <label for="floatingInput">Category Name</label>
                    @error('name')
                        <span class="text-danger">{{ $message }} </span>
                    @enderror
                </div>
                <button class="btn btn-primary" type="submit">Submit</button>
              </div>
             
            </div>
            
          </form>
          
          
        </div>
      </div>
      
    </div>
    <!-- [ form-element ] end -->
  </div>

