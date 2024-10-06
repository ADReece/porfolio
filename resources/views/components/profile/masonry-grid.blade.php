<div x-data="{ loading : true}" class="masonry-container w-full min-h-screen">
    <div class="fixed top-0 left-o m-0 overflow-hidden w-full h-full bg-gray-100 dark:bg-gray-900" x-show="loading" x-on:load.window="loading = false; initMasonry()" x-transition>
        <div class="loader"></div>
    </div>

    <div class="masonry mx-auto">
        <div class="masonry-sizer"></div>
        <div class="masonry-gutter-sizer"></div>
        @foreach($media as $m)
            <div class="masonry-item">
                <a data-fslightbox
                    data-caption="{{ $m->caption }}"
                    href="{{ $m->getAwsMedia() }}"
                >
                    <img src="{{ $m->getAwsThumbnail() }}" />
                </a>
            </div>
        @endforeach
        @if(count($media) == 0 && env('APP_ENV') !== 'live')
            <!-- Placeholders for testing -->
            <div class="masonry-item" >
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
    @php($cols = $mobile_cols)
@endif

@php($size = (100 / $cols) - 1)

<style>
    .masonry-item,
    .masonry-sizer { width:{{$size}}%;margin-top:5px; box-sizing:border-box; }
    .masonry-sizer { display:none; }
    .masonry-item > img {width: 100%;}
    .masonry-gutter-sizer { width:5px; }

    .loader,
    .loader:after {
        border-radius: 50%;
        width: 10em;
        height: 10em;
    }
    .loader {
        /*margin:0 auto;*/
        top:45%;
        left:38%;
        font-size: 10px;
        position: relative;
        text-indent: -9999em;
        border-top: 1.1em solid rgba(255, 255, 255, 0.2);
        border-right: 1.1em solid rgba(255, 255, 255, 0.2);
        border-bottom: 1.1em solid rgba(255, 255, 255, 0.2);
        border-left: 1.1em solid #ffffff;
        -webkit-transform: translateZ(0);
        -ms-transform: translateZ(0);
        transform: translateZ(0);
        -webkit-animation: load8 1.1s infinite linear;
        animation: load8 1.1s infinite linear;
    }
    @-webkit-keyframes load8 {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }
    @keyframes load8 {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }
</style>

<script>
    //Initalise Masonry.
    function initMasonry() {
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
    window.onload = () => {


    }

</script>
