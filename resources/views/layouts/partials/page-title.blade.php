<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    @if(isset($parentTitle))
                    <li class="breadcrumb-item">
                        <a href="{{ $parentRoute ?? 'javascript: void(0);' }}">{{ $parentTitle }}</a>
                    </li>
                    @endif

                    @if(isset($subTitle))
                    <li class="breadcrumb-item">
                        <a href="{{ $subTitleRoute ?? 'javascript: void(0);' }}">{{ $subTitle }}</a>
                    </li>
                    @endif

                    <li class="breadcrumb-item active">
                        <a href="{{ $titleRoute ?? 'javascript: void(0);' }}" class="text-primary fw-medium">
                            {{ $title }}
                        </a>
                    </li>
                </ol>
            </div>
            <h4 class="page-title">
                <a href="{{ $titleRoute ?? 'javascript: void(0);' }}" class="text-dark">
                    {{ $title }}
                </a>
            </h4>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->
