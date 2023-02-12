<style>
    .grid-item { width: 200px; border: red 2px; }
    .grid-item--width2 { width: 400px; }
</style>

<div class="masonry-grid" onload="initGrid()">
    <div class="grid-item">...</div>
    <div class="grid-item grid-item--width2">...</div>
    <div class="grid-item">...</div>
    <div class="grid-item">...</div>
    <div class="grid-item grid-item--width2">...</div>
    <div class="grid-item">...</div>
    <div class="grid-item">...</div>
    <div class="grid-item grid-item--width2">...</div>
    <div class="grid-item">...</div>
    <div class="grid-item">...</div>
    <div class="grid-item grid-item--width2">...</div>
    <div class="grid-item">...</div>
</div>

<script>
    const initGrid = () => {
        var elem = document.querySelector('.masonry-grid');
        var msnry = new Masonry( elem, {
            itemSelector: '.grid-item',
            columnWidth: 200
        });
        console.log(msnry);
    }

    window.onload = () => {
        initGrid();
    }

</script>
