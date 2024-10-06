<div class="card-body pt-3">
    <div class="table-responsive">
        <div class="datatable-top">
            <button wire:click="exportExcel" class="btn btn-light-primary mb-1 btn-sm csv">Export Excel</button>
            <div class="datatable-search">
                <input wire:model.live="search" class="datatable-input" placeholder="Search..." type="search" title="Search within table" aria-controls="pc-dt-satetime-sorting">
            </div>
        </div>
        
        <!-- [ Main Content ] start -->
        <div class="row">
            <!-- [ sample-page ] start -->
            <div class="col-sm-12">
              <div class="card table-card">
                <div class="card-body">
                  
                  <div class="table-responsive">
                    <table class="table table-hover tbl-product" id="pc-dt-simple">
                      <thead>
                        <tr>
                          <th class="text-end">#</th>
                          <th>Product Detail</th>
                          <th>Categories</th>
                          <th class="text-end">Agent Price</th>
                          <th class="text-end">Qty</th>
                          <th class="text-center">Stock Date</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($products as $product)
                        <tr wire:key="{{$product->id }}">
                          <td class="text-end">{{$loop->iteration }}</td>
                          <td>
                            <div class="row">
                              <div class="col-auto pe-0">
                                @php
                                    
                                    $firstImage = $images->where('product_id', $product->id)->first();
                                @endphp
                                @if ($firstImage)
                                    <img src="{{ $firstImage->image_path }}" alt="user-image" class="wid-40 rounded">
                                @else
                                    <img src="{{ asset('assets/no-image.png') }} " alt="image" class="wid-40 rounded">
                                @endif
                              </div>
                              <div class="col">
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                <!-- <p class="text-muted f-12 mb-0">Apple Watch SE Smartwatch </p> -->
                              </div>
                            </div>
                          </td>
                          <td>{{ $product->category ? $product->category->name : 'No category' }},{{ $product->subcategory ? $product->subcategory->name : 'No category' }}</td>
                          <td class="text-end">₦{{ number_format($product->agent_price, 2) }}</td>
                          <td class="text-end">{{ $product->quantity }}</td>
                          
                          <td class="text-center">
                            {{ $product->stock_date }}
                            <div class="prod-action-links">
                              <ul class="list-inline me-auto mb-0">
                                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="View">
                                    <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="show">
                                        <a href="{{ route('vendor.product.show', $product->id) }}" class="avtar avtar-xs btn-link-success btn-pc-default">
                                          <i class="ti ti-eye f-18"></i>
                                        </a>
                                      </li>
                                    
                                </li>
                                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Edit">
                                  <a href="{{ route('vendor.product.edit', $product->id) }}" class="avtar avtar-xs btn-link-success btn-pc-default">
                                    <i class="ti ti-edit-circle f-18"></i>
                                  </a>
                                </li>
                                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Delete">
                                  <a href="#" onclick="confirmDeletion(event, @this, {{ $product->id }})" class="avtar avtar-xs btn-link-danger btn-pc-default">
                                    <i class="ti ti-trash f-18"></i>
                                  </a>
                                  <script>
                                    function confirmDeletion(event, component, id) {
                                        event.preventDefault(); // Prevent the default anchor action
                                
                                        if (confirm('Are you sure you want to delete this?')) {
                                            component.call('softDelete', id);
                                        }
                                    }
                                </script>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                              <div class="alert alert-primary d-flex align-items-center" role="alert">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:"><use xlink:href="#info-fill"/></svg>
                                <div>
                                  No Products 
                                </div>
                              </div>
                            </td>
                        </tr>
                        @endforelse
                        
                        
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <!-- [ sample-page ] end -->
          </div>
          <!-- [ Main Content ] end -->
        <div class="mx-4">
            {{ $products->links() }}
        </div>
    </div>
    
    
</div>
