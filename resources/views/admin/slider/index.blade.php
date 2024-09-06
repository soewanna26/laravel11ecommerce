@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Slider</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Slider</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <form class="form-search">
                            <fieldset class="name">
                                <input type="text" placeholder="Search here..." class="" name="name"
                                    tabindex="2" value="" aria-required="true" required="">
                            </fieldset>
                            <div class="button-submit">
                                <button class="" type="submit"><i class="icon-search"></i></button>
                            </div>
                        </form>
                    </div>
                    <a class="tf-button style-1 w208" href="{{ route('admin.slider.create') }}"><i class="icon-plus"></i>Add
                        new</a>
                </div>
                <div class="wg-table table-all-user">
                    <table class="table table-striped table-bordered">
                        @include('message')
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Tagline</th>
                                <th>Title</th>
                                <th>Subtitle</th>
                                <th>Link</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sliders as $slider)
                                <tr>
                                    <td>{{ $slider->id }}</td>
                                    <td class="pname">
                                        <div class="image">
                                            @if ($slider->image != '')
                                                <img src="{{ asset('storage/sliderImage/' . $slider->image) }}"
                                                    alt="{{ $slider->name }}" class="image">
                                            @else
                                                <img src="https://placehold.co/150x150?text=No Image" alt="No Image"
                                                    class="image">
                                            @endif

                                        </div>
                                    </td>
                                    <td>{{ $slider->tagline }}</td>
                                    <td>{{ $slider->title }}</td>
                                    <td>{{ $slider->subtitle }}</td>
                                    <td>{{ $slider->link }}</td>
                                    <td>
                                        <div class="list-icon-function">
                                            <a href="{{ route('admin.slider.edit', ['id' => $slider->id]) }}">
                                                <div class="item edit">
                                                    <i class="icon-edit-3"></i>
                                                </div>
                                            </a>
                                            <form action="{{ route('admin.slider.delete', ['id' => $slider->id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="item text-danger delete">
                                                    <i class="icon-trash-2"></i>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    @if (!empty($sliders))
                        {{ $sliders->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function() {
            $('.delete').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                swal({
                    title: "Are you sure?",
                    text: "You want to delete this record?",
                    type: "warning",
                    buttons: ["No", "Yes"],
                    confirmButtonColor: "#dc3545"
                }).then(function(result) {
                    if (result) {
                        form.submit();
                    }
                })
            })
        })
    </script>
@endpush
