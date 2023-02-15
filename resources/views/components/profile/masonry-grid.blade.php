<div class="masonry-container w-100 min-h-screen">
    <div class="masonry">
        <div class="masonry-sizer"></div>
        <div class="masonry-gutter-sizer"></div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=450&h=660&v=1">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=660&h=450&v=6">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=450&h=660&v=3">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=450&h=660&v=4">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=660&h=450&v=7">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=660&h=450&v=5">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=450&h=660&v=3">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=450&h=660&v=4">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=450&h=660&v=1">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=450&h=660&v=2">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=450&h=660&v=3">
        </div>
        <div class="masonry-item">
            <img class="w-full" src="https://api.lorem.space/image/movie?w=450&h=660&v=4">
        </div>
    </div>
</div>

<style>
    .masonry-container { padding:20px; }
    .masonry-item,
    .masonry-sizer { width:32%; }
    .masonry img {width: 100%; padding-bottom:20px;}
    .masonry-gutter-sizer { width:2%; }
</style>

<script>
    window.onload = () => {
        let masonry_el = $('.masonry');
        let masonry = new Masonry('.masonry', {
            itemSelector: '.masonry-item',
            columnWidth: '.masonry-sizer',
            percentPosition: true,
            stagger: 30,
            resize: true,
            gutter: '.masonry-gutter-sizer',
        });
    }

</script>
