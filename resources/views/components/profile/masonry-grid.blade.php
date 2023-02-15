<div class="masonry-container w-100 min-h-screen">
    <div class="masonry mx-auto">
        <div class="masonry-sizer"></div>
        <div class="masonry-gutter-sizer"></div>
        @foreach($user?->media as $media)
            <div class="masonry-item">
                <img class="w-full" src="{{ $media->url }}">
            </div>
        @endforeach
        <div class="masonry-item">
            <img class="w-full" src="https://fakeimg.pl/600">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://fakeimg.pl/400x600">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://fakeimg.pl/900x400">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://fakeimg.pl/500">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://fakeimg.pl/400">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://fakeimg.pl/600x420">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://fakeimg.pl/400">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://fakeimg.pl/900">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://fakeimg.pl/500">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://fakeimg.pl/400">
        </div>
    </div>
</div>
@php($input = 3);
@php($size = (100 / $input) - 1)

<style>
    .masonry-item,
    .masonry-sizer { width:{{$size}}%;margin-top:5px; box-sizing:border-box; }
    .masonry-sizer { display:none; }
    .masonry img {width: 100%;}
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

        const filterMasonry = (tag) => {
            let items = $(`.${tag}`); //Needs to be not this tag.
            items.each((item) => {
                item.removeClass('masonry-item');
            })
        }

    }

</script>
