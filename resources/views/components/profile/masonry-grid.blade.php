<style>
    .masonry-container {
        width:100%;
    }
    .masonry-grid {

    }

    /* reveal masonry-grid after images loaded */
    .masonry-grid.are-images-unloaded {
        opacity: 1; /*CHANGE THIS*/
    }

    .masonry-grid__item,
    .masonry-grid__col-sizer {
        width: 33%;
    }

    .masonry-grid__gutter-sizer { width: 2%; }

    /* hide by default */
    .masonry-grid.are-images-unloaded .image-masonry-grid__item {
        opacity: 0;
    }

    .masonry-grid__item {
        margin-bottom: 20px;
        float: left;
    }

    .masonry-grid__item--height1 { height: 140px; background: #EA0; }
    .masonry-grid__item--height2 { height: 220px; background: #C25; }
    .masonry-grid__item--height3 { height: 300px; background: #19F; }

    .masonry-grid__item--width2 { width: 66%; }

    .masonry-grid__item img {
        display: block;
        max-width: 100%;
    }

    .page-load-status {
        display: none; /* hidden by default */
        padding-top: 20px;
        border-top: 1px solid #DDD;
        text-align: center;
        color: #777;
    }

    /* loader ellips in separate pen CSS */

</style>
<div class="masonry-container">
    <div class="masonry-grid are-images-unloaded">
      <div class="masonry-grid__col-sizer"></div>
      <div class="masonry-grid__gutter-sizer"></div>
      <div class="masonry-grid__item masonry-grid__item--height2"></div>
      <div class="masonry-grid__item masonry-grid__item--width2">
        <img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/82/orange-tree.jpg" alt="orange tree" />
      </div>
      <div class="masonry-grid__item masonry-grid__item--height3"></div>
      <div class="masonry-grid__item masonry-grid__item--height1"></div>
      <div class="masonry-grid__item masonry-grid__item--height2"></div>
      <div class="masonry-grid__item">
        <img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/82/look-out.jpg" alt="look out" />
      </div>

      <div class="masonry-grid__item masonry-grid__item--height1"></div>
      <div class="masonry-grid__item masonry-grid__item--height3"></div>
      <div class="masonry-grid__item masonry-grid__item--height1"></div>
      <div class="masonry-grid__item masonry-grid__item--height3"></div>
      <div class="masonry-grid__item masonry-grid__item--height1"></div>
      <div class="masonry-grid__item masonry-grid__item--height1"></div>
      <div class="masonry-grid__item masonry-grid__item--width2">
        <img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/82/raspberries.jpg" alt="rasberries" />
      </div>
      <div class="masonry-grid__item masonry-grid__item--height2"></div>
      <div class="masonry-grid__item masonry-grid__item--height2"></div>
      <div class="masonry-grid__item masonry-grid__item--height3"></div>
      <div class="masonry-grid__item masonry-grid__item--height1"></div>
      <div class="masonry-grid__item masonry-grid__item--height2"></div>
    </div>

    <div class="page-load-status">
      <div class="loader-ellips infinite-scroll-request">
        <span class="loader-ellips__dot"></span>
        <span class="loader-ellips__dot"></span>
        <span class="loader-ellips__dot"></span>
        <span class="loader-ellips__dot"></span>
      </div>
      <p class="infinite-scroll-last">End of content</p>
      <p class="infinite-scroll-error">No more pages to load</p>
    </div>
</div>


<script>
    const initGrid = () => {
        let masonry = document.querySelector('.masonry-grid');

        let msnry = new Masonry( masonry, {
            itemSelector: 'none', // select none at first
            columnWidth: '.masonry-grid__col-sizer',
            gutter: '.masonry-grid__gutter-sizer',
            percentPosition: true,
            stagger: 1,
            // nicer reveal transition
            visibleStyle: { transform: 'translateY(0)', opacity: 1 },
            hiddenStyle: { transform: 'translateY(100px)', opacity: 0 },
        });
    }

    window.onload = () => {
        initGrid();

        // let infScroll = new InfiniteScroll( masonry-grid, {
        //     path: null,
        //     append: '.masonry-grid__item',
        //     outlayer: msnry,
        //     status: '.page-load-status',
        // });
    }


//-------------------------------------//


// // initial items reveal
// imagesLoaded( masonry-grid, function() {
//   masonry-grid.classList.remove('are-images-unloaded');
//   msnry.options.itemSelector = '.masonry-grid__item';
//   let items = masonry-grid.querySelectorAll('.masonry-grid__item');
//   msnry.appended( items );
// });

// //-------------------------------------//
// // hack CodePen to load pens as pages

// var nextPenSlugs = [
//   '202252c2f5f192688dada252913ccf13',
//   'a308f05af22690139e9a2bc655bfe3ee',
//   '6c9ff23039157ee37b3ab982245eef28',
// ];

// function getPenPath() {
//   let slug = nextPenSlugs[ this.loadCount ];
//   if ( slug ) {
//     return `/desandro/debug/${slug}`;
//   }
// }

// //-------------------------------------//
// // init Infinte Scroll

// let infScroll = new InfiniteScroll( masonry-grid, {
//   path: getPenPath,
//   append: '.masonry-grid__item',
//   outlayer: msnry,
//   status: '.page-load-status',
// });


</script>
