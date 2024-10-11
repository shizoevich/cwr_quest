<div class="container-fluid">
    <div class="row">
        <div class="col-sm-8" style="padding-left:0;padding-right:0;">
            <video id="video" class="video-js vjs-big-play-centered" height="480" style="width:100%" controls>
            </video>
        </div>
        <div class="col-sm-4" style="padding-left:0;padding-right:0;">
            <div class="vjs-playlist"></div>
        </div>
    </div>
</div>

@section('scripts')
    @parent 
    <script src="{{ asset('plugins/videojs/video.min.js') }}"></script>
    <script src="{{ asset('plugins/videojs/videojs-playlist.min.js') }}"></script>
    <script src="{{ asset('plugins/videojs/videojs-playlist-ui.min.js') }}"></script>
    
    <script>
        let player = videojs('video', {
            preload: true,
            autoplay: false,
            controls: true,
            controlBar: {
                liveDisplay: true,
                pictureInPictureToggle: false
            }
        });

        player.playlist([
            @foreach($videoTrainings as $videoTraining)
            {
                name: '{{ $videoTraining->title }}',
                description: '{{ $videoTraining->description }}',
                sources: [
                    { src: '{{ $videoTraining->source_url }}', type: '{{ $videoTraining->source_type }}' },
                ],
                duration: {{ $videoTraining->duration }},
            },
            @endforeach
        ]);

        // Initialize the playlist-ui plugin with no option (i.e. the defaults).
        player.playlistUi();
    </script>
@endsection