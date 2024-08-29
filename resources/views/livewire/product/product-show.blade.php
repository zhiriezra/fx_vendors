<div class="row">
    <a href="{{ route('vendor.product.edit', $product->id) }}"><span class="badge bg-secondary mb-4">Edit</span></a>
    <div class="col-12">
        <div class="card table-card">            
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Batch Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->batch_number }}</td>
                                
                            </tr>
                        </tbody>

                        <thead>
                            <tr>
                                <th>Manufacturer</th>
                                <th>Category</th>
                                <th>Sub Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $product->manufacturer }}</td>
                                <td>{{ $product->category ? $product->category->name : 'No category' }}</td>
                                <td>{{ $product->subcategory ? $product->subcategory->name : 'No Subcategory' }}</td>
                            </tr>
                        </tbody>

                        <thead>
                            <tr>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Agent Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $product->quantity }}</td>
                                <td>₦{{ number_format($product->unit_price, 2) }}</td>
                                <td>₦{{ number_format($product->agent_price, 2) }}</td>
                            </tr>
                        </tbody>

                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Stock Date</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $product->type }}</td>
                                <td>{{ $product->stock_date }}</td>
                            </tr>
                        </tbody>
    
                        <thead>
                            <tr>
                                <th colspan="3">Description</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="3">{{ $product->description }}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="col-12">
                        <div class="card">
                            <div class="row">
                                <div class="card-header col-6">
                                    <h4>Product Images</h4>
                                          
                                </div>
                                <div class="card-header col-6">
                                    <button wire:click="showForm" class="btn btn-primary pt-1 pb-1">Add Image</button>
                                </div>
                            </div>
                            @if(Session::has('message'))
                                <div class="alert alert-success d-flex align-items-center" role="alert">
                                    <i class="fas fa-check-circle px-3"></i>
                                    <div> {{ Session::get('message') }}</div>
                                </div>     
                            @endif  
                            <div class="card-body">
                                @if($showForm)
                                <div class="container pt-3">
                                    <form wire:submit.prevent="addImage">
                                        <div class="form-group">
                                            <input type="file" wire:model="newImage" class="form-control" required>
                                            
                                                @error('newImage') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-2 pt-1 pb-1">Upload</button>
                                        <button type="button" wire:click="hideForm" class="btn btn-primary mt-2 pt-1 pb-1">Cancel</button>
                                    </form>
                                </div>
                                    
                                @endif
                                <div class="grid row g-3 pt-4 container">
                                    @foreach ($productImage as $image )
                                        @if ($image->product_id == $product->id)
                                            <div class="col-xl-3 col-md-4 col-sm-6">
                                                <a class="card-gallery" data-fslightbox="gallery" href="{{ $image->image_path }}">
                                                    <img class="img-fluid" src="{{ $image->image_path }}" alt="Card image" >
                    
                                                </a>
                                                <div class="pt-2" style="text-align: center;">
                                                    <button type="submit" class="btn btn-danger mt-2 pt-1 pb-1"  wire:click="deleteImage({{ $image->id }})" wire:navigate>Delete</button>
                                    
                                                </div>
                                
                                            </div>
                                        @endif
                                    @endforeach
                                </div> 
                            </div>
                        </div>
                    </div>        
                </div>
            </div>
        </div>
    </div>
</div>