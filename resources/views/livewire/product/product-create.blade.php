<form wire:submit.prevent="createProduct">
    <p class="text-danger">* indicates compulsory fields</p>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Product Name" wire:model="name">
            <label for="floatingInput"><span class="text-danger">*</span> Product Name</label>
            @error('name')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
            
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" wire:model="description" placeholder="Desciption"></textarea>
                <label for="floatingPassword"><span class="text-danger">*</span> Desciption</label>
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
                <label for="floatingselect1"> <span class="text-danger">*</span> Category</label>
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
                <label for="floatingselect1"> <span class="text-danger">*</span> Sub Category</label>
                @error('subcategory_id')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="date" class="form-control" id="floatingInput" placeholder=" Stock Date" wire:model="stock_date">
            <label for="floatingInput"><span class="text-danger">*</span> Stock Date</label>
            @error('stock_date')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
        </div>
        <div class="col-md-6">
        <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Identification Number" wire:model="manufacturer">
            <label for="floatingPassword"><span class="text-danger">*</span> Manufacturer</label>
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
            <label for="floatingInput"><span class="text-danger">*</span> Type</label>
            @error('type')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <input type="number" class="form-control" id="floatingInput" placeholder="Permanent Address" wire:model="quantity">
                <label for="floatingPassword"><span class="text-danger">*</span> Quantity</label>
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
            <label for="floatingInput"><span class="text-danger">*</span> Unit Price</label>
            @error('unit_price')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="number" step="0.01" class="form-control" id="floatingInput" placeholder="Agent Price" wire:model="agent_price">
            <label for="floatingInput"><span class="text-danger">*</span> Agent Price</label>
            @error('agent_price')
                <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="file" class="form-control" id="floatingInput" placeholder=" Images" wire:model="images" multiple>
            <label for="floatingInput">Images</label>
            @error('images.*') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            @if ($errors->has('images'))
                <span class="text-danger">{{ $errors->first('images') }}</span>
            @endif
            @if (count($images) > $maxImages)
                <span class="text-danger">You cannot upload more than {{ $maxImages }} images.</span>
            @endif
        </div>
    </div>
    <button class="btn btn-primary" type="submit">Submit</button>
</form>