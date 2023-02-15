<div class="masonry-container w-100 min-h-screen">
    <div class="masonry mx-auto">
        <div class="masonry-sizer"></div>
        <div class="masonry-gutter-sizer"></div>
        @foreach($user?->media as $media)
            <div class="masonry-item">
                <img class="w-full" src="{{ $media->url }}">
            </div>
        @endforeach
    </div>
</div>

<style>
    .masonry-container { padding:20px; }
    .masonry-item,
    .masonry-sizer { width:32%; margin-bottom:10px; }
    .masonry img {width: 100%;}
    .masonry-gutter-sizer { width:10px }
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
