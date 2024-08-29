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
                    <th scope="col">Agent Price</th>
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
                                <!-- Get the first image associated with this product -->
                                @php
                                    
                                    $firstImage = $images->where('product_id', $product->id)->first();
                                @endphp
                                @if ($firstImage)
                                <a href="{{ route('vendor.product.show', $product->id) }}">

                                <div class="d-inline-block align-middle">
                                    <img src="{{ $firstImage->image_path }}" alt="image" class="img-radius align-top m-r-15" style="width:40px;">
                                    <div class="d-inline-block">
                                     <h6 class="mt-3">{{ $product->name }}</h6>
                                    <!-- <p class="m-b-0 text-primary">Android developer</p> -->
                                    </div>
                                </div></a>
                                @else
                                <div class="d-inline-block">
                                    <a href="{{ route('vendor.product.show', $product->id) }}"><h6 class="mt-3">{{ $product->name }}</h6></a> 
                                   <!-- <p class="m-b-0 text-primary">Android developer</p> -->
                                </div>
                                @endif
                            </td>
                    <!-- <td><a href="{{ route('vendor.product.show', $product->id) }}"> {{ $product->name }} </a></td> -->
        
                    <td>{{ $product->category ? $product->category->name : 'No category' }}</td>
                    <td>₦{{ number_format($product->agent_price, 2) }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>{{ $product->stock_date }}</td>
                    <td>
                        <!-- <a href="{{ route('vendor.product.show', $product->id) }}" wire:navigate class="avtar avtar-xs btn-link-primary">
                        <i class="ti ti-eye f-20" data-bs-toggle="tooltip" data-bs-placement="top" title="View"></i>
                        </a> -->
                        <a href="{{ route('vendor.product.edit', $product->id) }}" wire:navigate class="avtar avtar-xs btn-link-info">
                        <i class="ti ti-edit f-20" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"></i>
                        </a>
                        <a href="#" onclick="confirmDeletion(event, @this, {{ $product->id }})" class="avtar avtar-xs btn-link-danger">
                            <i class="ti ti-trash f-20" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"></i>
                        </a>
                        
                        <script>
                            function confirmDeletion(event, component, id) {
                                event.preventDefault(); // Prevent the default anchor action
                        
                                if (confirm('Are you sure you want to delete this?')) {
                                    component.call('softDelete', id);
                                }
                            }
                        </script>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4"><h4 style="text-align: center;"><i class="fab fa-product-hunt"></i> 0 Products</h4></td>
                </tr>
                @endforelse

             
                
            </tbody>
        </table>
        <div class="mx-4">
            {{ $products->links() }}
        </div>
    </div>
    
    
</div>
