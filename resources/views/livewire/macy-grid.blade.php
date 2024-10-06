<div x-data="data">
    <div id="macy-container">
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
    <div x-show="!loading" class="bg-red-700 w-full text-2xl" x-intersect.full="$wire.load();">Here</div>


    @php($cols = 4);
    @php($mobile_cols = 2)

    @if(preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]))
        @php($cols = $mobile_cols)
    @endif

    <script>
        function data(){
            return {
                loading : false,
                macy : null,
                init(){
                    this.initMacy()
                },
                initMacy(){
                    this.macy = Macy({
                        container: '#macy-container',
                        trueOrder: false,
                        waitForImages: true,
                        margin: 2,
                        columns: {!! $cols !!},
                        breakAt: {
                            1200: 5,
                            940: 3,
                            520: 2,
                            400: 1
                        }
                    });
                },
                recalculateMacy(){
                    this.macy.recalculate();
                }
            }
        }

        window.addEventListener('load', function () {
            data().recalculateMacy();
        })
    </script>
</div>