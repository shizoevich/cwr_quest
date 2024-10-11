@extends('layouts.app')

@section('style')
    <link href="{{ asset('plugins/videojs/video-js.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('plugins/videojs/videojs-playlist-ui.css') }}" rel="stylesheet" />
    <link href="//unpkg.com/@videojs/themes@1.0.1/dist/fantasy/index.css" rel="stylesheet" />
    <style rel="stylesheet">
        .vjs-theme-fantasy {
            --vjs-theme-fantasy--primary: #3097D1!important;
        }
        .vjs-playlist-item.vjs-selected {
            color: #3097D1;
        }
        .vjs-playlist {
            max-height: 480px;
        }
        @media (max-width: 520px) {
            .vjs-playlist-vertical .vjs-playlist-thumbnail-placeholder {
                height: 38px;
            }
            .vjs-playlist {
                max-height:172px;
            }
        }

        .harassment-certificate-file-upload {
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid #a3aebc;
            border-radius: 4px;
            padding: 10px;
            width: fit-content;
            min-width: 350px;
            background: #fff;
        }

        #harassment-certificate-top-message,
        #harassment-certificate-bottom-message {
            margin-top: 10px;
        }

        .well-title {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .well-loader {
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background-color: #f5f5f5;
            border-radius: 4px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .well-loader img {
            height: 60px;
            width: 60px;
        }
    </style>
@endsection

@section('content')
    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    @php
                        $currentTab = app('request')->input('tab');
                        if (empty($currentTab)) {
                            $currentTab = $videoTrainings->isEmpty() ? 'hippa' : 'professional';
                        }
                    @endphp

                    <ul class="nav nav-tabs">
                        @if($videoTrainings->isNotEmpty())
                        <li class="{{ $currentTab === 'professional' ? 'active' : '' }}">
                            <a href="#professional" data-toggle="tab">Professional Training</a>
                        </li>
                        @endif

                        <li class="{{ $currentTab === 'hippa' ? 'active' : '' }}">
                            <a href="#hippa" data-toggle="tab">HIPPA Compliance Training</a>
                        </li>
                        <li class="{{ $currentTab === 'harassment' ? 'active' : '' }}">
                            <a href="#harassment" data-toggle="tab">Sexual Harassment Prevention Training</a>
                        </li>
                    </ul>
            
                    <div class="tab-content">
                        @if($videoTrainings->isNotEmpty())
                        <div class="tab-pane {{ $currentTab === 'professional' ? 'active' : '' }}" id="professional">
                            @include('training.tabs.professional')
                        </div>
                        @endif

                        <div class="tab-pane {{ $currentTab === 'hippa' ? 'active' : '' }}" id="hippa">
                            @include('training.tabs.hippa')
                        </div>
                        <div class="tab-pane {{ $currentTab === 'harassment' ? 'active' : '' }}" id="harassment">
                            @include('training.tabs.harassment')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@parent
<script>
    $(document).ready(function() {
        $('a[data-toggle="tab"]').on('click', function(e) {
            e.preventDefault();

            var tabName = $(this).attr('href').replace('#', '');
            window.history.pushState(null, null, '?tab=' + tabName);

            $(this).tab('show');
        });
    });
</script>
@endsection
