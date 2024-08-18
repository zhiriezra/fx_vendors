<form wire:submit.prevent="updateProduct">
    
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="First name" wire:model="name">
            <label for="floatingInput">Product Name</label>
            @error('name')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
            
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" wire:model="description"></textarea>
                <label for="floatingPassword">Desciption</label>
                @error('description')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <select class="form-control" id="identification" aria-label="Floating label select" wire:model="category_id">
                <option selected>---Select Category---</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
                </select>
                <label for="floatingselect1">Category</label>
                @error('category_id')
                <span class="text-danger">{{ $message }} </span>
                @enderror
        </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <select class="form-control" id="identification" aria-label="Floating label select" wire:model="subcategory_id" id="subcategory">
                    <option selected>---Select Subcategory---</option>
                    @foreach($subcategories as $subcategory)
                        <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                    @endforeach
                </select>
                <label for="floatingselect1">Sub Category</label>
                @error('subcategory_id')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
        <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Identification Number" wire:model="manufacturer">
            <label for="floatingPassword">Manufacturer</label>
            @error('manufacturer')
                <span class="text-danger">{{ $message }} </span>
            @enderror
        </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Type" wire:model="type">
            <label for="floatingInput">Type</label>
            @error('type')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <input type="number" class="form-control" id="floatingInput" placeholder="Permanent Address" wire:model="quantity">
                <label for="floatingPassword">Quantity</label>
                @error('quantity')
                    <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="number" step="0.01" class="form-control" id="floatingInput" placeholder="Unit Price" wire:model="unit_price">
            <label for="floatingInput">Unit Price</label>
            @error('unit_price')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="number" step="0.01" class="form-control" id="floatingInput" placeholder="Agent Price" wire:model="agent_price">
            <label for="floatingInput">Agent Price</label>
            @error('agent_price')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="date" class="form-control" id="floatingInput" placeholder=" Stock Date" wire:model="stock_date">
            <label for="floatingInput">Stock Date</label>
            @error('stock_date')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
    </div>
    <button class="btn btn-primary" type="submit">Submit</button>
</form>