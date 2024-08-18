<div class="card-body pt-3">
    <div class="table-responsive">
        <div class="datatable-top">
            <button class="btn btn-light-primary mb-1 btn-sm csv">Export PDF</button>
            <button class="btn btn-light-primary mb-1 btn-sm csv">Export Excel</button>
            <div class="datatable-search">
                <input wire:model.live="search" class="datatable-input" placeholder="Search..." type="search" title="Search within table" aria-controls="pc-dt-satetime-sorting">
            </div>
        </div>
        <table class="table table-hover " id="pc-dt-simple">
            <thead>
                <tr>
                    <!-- <th scope="col">Image</th> -->
                    <th scope="col">Product</th>
                    <th scope="col">Category</th>
                    <th scope="col">Unit Price</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Stock Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Search section start -->
                @forelse($products as $product)
                <tr wire:key="{{$product->id }} ">
                            <td>
                                @foreach ($images as $image )
                                @if ($image->product_id == $product->id)
                                <a href="{{ route('vendor.product.show', $product->id) }}">

                                <div class="d-inline-block align-middle">
                                    <img src="{{ Storage::url($image->image_path) }}" alt="image" class="img-radius align-top m-r-15" style="width:40px;">
                                    <div class="d-inline-block">
                                     <h6 class="mt-3">{{ $product->name }}</h6>
                                    <!-- <p class="m-b-0 text-primary">Android developer</p> -->
                                    </div>
                                </div></a>
                                @endif
                                @endforeach
                            </td>
                    <!-- <td><a href="{{ route('vendor.product.show', $product->id) }}"> {{ $product->name }} </a></td> -->
        
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td>{{ $product->unit_price }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>{{ $product->stock_date }}</td>
                    <td>
                        <!-- <a href="{{ route('vendor.product.show', $product->id) }}" wire:navigate class="avtar avtar-xs btn-link-primary">
                        <i class="ti ti-eye f-20" data-bs-toggle="tooltip" data-bs-placement="top" title="View"></i>
                        </a> -->
                        <a href="{{ route('vendor.product.edit', $product->id) }}" wire:navigate class="avtar avtar-xs btn-link-info">
                        <i class="ti ti-edit f-20" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"></i>
                        </a>
                        <a href="#" class="avtar avtar-xs btn-link-danger">
                            <i class="ti ti-trash f-20" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">No products</td>
                </tr>
                @endforelse

             
                
            </tbody>
        </table>
    </div>
    
    
</div>
