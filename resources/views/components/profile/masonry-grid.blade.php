<div class="masonry-container w-100 min-h-screen">
    <div class="masonry mx-auto">
        <div class="masonry-sizer"></div>
        <div class="masonry-gutter-sizer"></div>
        @foreach($user?->media as $media)
            <div class="masonry-item">
                <a data-fslightbox
                    data-caption="{{ $media->caption }}"
                    href="{{ $media->getAwsMedia() }}"
                >
                    <img src="{{ $media->getAwsMedia() }}" />
                </a>
            </div>
        @endforeach
        @if(count($user->media) == 0)
            <!-- Placeholders for testing -->
            <div class="masonry-item">
                <a data-fslightbox
                data-thumb="https://fakeimg.pl/600"
                href="https://fakeimg.pl/600">
                    <img class="w-full" src="https://fakeimg.pl/600">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/400x600">
                    <img class="w-full" src="https://fakeimg.pl/400x600">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/900x400">
                    <img class="w-full" src="https://fakeimg.pl/900x400">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/500x1200">
                    <img class="w-full" src="https://fakeimg.pl/500x1200">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/600">
                    <img class="w-full" src="https://fakeimg.pl/600">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/600x420">
                    <img class="w-full" src="https://fakeimg.pl/600x420">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/400">
                    <img class="w-full" src="https://fakeimg.pl/400">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/600">
                    <img class="w-full" src="https://fakeimg.pl/600">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/400x600">
                    <img class="w-full" src="https://fakeimg.pl/400x600">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/900x400">
                    <img class="w-full" src="https://fakeimg.pl/900x400">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/500x1200">
                    <img class="w-full" src="https://fakeimg.pl/500x1200">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/600">
                    <img class="w-full" src="https://fakeimg.pl/600">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/600x420">
                    <img class="w-full" src="https://fakeimg.pl/600x420">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/400">
                    <img class="w-full" src="https://fakeimg.pl/400">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/600">
                    <img class="w-full" src="https://fakeimg.pl/600">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/400x600">
                    <img class="w-full" src="https://fakeimg.pl/400x600">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/900x400">
                    <img class="w-full" src="https://fakeimg.pl/900x400">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/500x1200">
                    <img class="w-full" src="https://fakeimg.pl/500x1200">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/600">
                    <img class="w-full" src="https://fakeimg.pl/600">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/600x420">
                    <img class="w-full" src="https://fakeimg.pl/600x420">
                </a>
            </div>
            <div class="masonry-item">
                <a data-fslightbox href="https://fakeimg.pl/400">
                    <img class="w-full" src="https://fakeimg.pl/400">
                </a>
            </div>
            <!-- End of placeholder images -->
        @endif
    </div>
</div>
@php($cols = 4);
@php($mobile_cols = 2)

@if(preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]))
    @php($$cols = $mobile_cols)
@endif

@php($size = (100 / $cols) - 1)

<style>
    .masonry-item,
    .masonry-sizer { width:{{$size}}%;margin-top:5px; box-sizing:border-box; }
    .masonry-sizer { display:none; }
    .masonry-item > img {width: 100%;}
    .masonry-gutter-sizer { width:5px; }
</style>

<script>
    //Initalise Masonry.
    window.onload = () => {
        let masonry_el = $('.masonry');
        let masonry = new Masonry('.masonry', {
            itemSelector: '.masonry-item',
            columnWidth: '.masonry-sizer',
            gutter: '.masonry-gutter-sizer',
            percentPosition: true,
            stagger: 30,
            resize: true,
        });

        fsLightbox.props.type = "image";

        const filterMasonry = (tag) => {
            let items = $(`.${tag}`); //Needs to be not this tag.
            items.each((item) => {
                item.removeClass('masonry-item');
            })
        }

    }

</script>
