<div x-data="data">
    <div class="masonry-container w-full min-h-screen">
        <div class="fixed top-0 left-o m-0 overflow-hidden w-full h-full bg-gray-100 dark:bg-gray-900" x-show="loading" x-on:load.window="loading = false; initMasonry();" x-transition>
            <div class="loader"></div>
        </div>

        <div id="masonry" class="masonry mx-auto">
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
                @for($i=0;$i < $amount; $i++)
                    @php($w = rand(100, 800))
                    @php($h = rand(100, 800))
                    <div class="masonry-item" >
                        <a data-fslightbox
                           data-thumb="https://fakeimg.pl/{{$w}}x{{$h}}"
                           href="https://fakeimg.pl/{{$w}}x{{$h}}">
                            <img class="w-full" src="https://fakeimg.pl/{{$w}}x{{$h}}">
                        </a>
                    </div>
                @endfor
            @endif
        </div>
    </div>
    <div x-show="!loading" class="w-full h-10" x-intersect.full="$wire.load();"></div>
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
            function data(){
                return {
                    loading : true,
                    initMasonry(){
                        window.msnry = new Masonry('.masonry', {
                            itemSelector: '.masonry-item',
                            columnWidth: '.masonry-sizer',
                            gutter: '.masonry-gutter-sizer',
                            percentPosition: true,
                            stagger: 30,
                            resize: true,
                        });
                    },
                    refreshGrid(){
                        window.msnry.destroy();
                        this.initMasonry();
                        window.msnry.layout();
                    }
                }
            }

            window.addEventListener('media-loaded', event => {
                data().refreshGrid();
            })

            // Select the node that will be observed for mutations
            var targetNode = document.getElementById('masonry');

            // Options for the observer (which mutations to observe)
            var config = { attributes: true, childList: true };

            // Callback function to execute when mutations are observed
            var callback = function(mutationsList) {
                for(var mutation of mutationsList) {
                    if (mutation.type == 'childList') {
                        console.log('A child node has been added or removed.');
                    }
                    else if (mutation.type == 'attributes') {
                        console.log('The ' + mutation.attributeName + ' attribute was modified.');
                    }
                }
            };

            // Create an observer instance linked to the callback function
            var observer = new MutationObserver(callback);

            // Start observing the target node for configured mutations
            observer.observe(targetNode, config);

            // Later, you can stop observing
            observer.disconnect();

            window.addEventListener('load', function () {
                data().refreshGrid();
            })

    </script>

    <script>
        const filterMasonry = (tag) => {
            let items = $(`.${tag}`); //Needs to be not this tag.
            items.each((item) => {
                item.removeClass('masonry-item');
            })
        }

    </script>
</div>
