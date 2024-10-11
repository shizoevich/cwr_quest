@extends('layouts.app')

@section('content')
    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="panel panel-default">
                    <div class="panel-heading text-right">
                        <a href="{{route('system-messages.add')}}" class="btn btn-success">Add Message</a>
                    </div>
                    <div class="panel-body">
                        @if(count($messages))
                        <table class="table">
                            <thead>
                            <tr>
                                <td>Date</td>
                                <td>Title</td>
                                <td>Text</td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($messages as $message)
                                <tr style="@if($message->only_for_admin){{"background-color:#fffcde;"}}@endif">
                                    <td>
                                        {{$message->created_at->format('m/d/Y h:i A')}}
                                    </td>
                                    <td>{!! $message->title !!}</td>
                                    <td>{!! $message->text !!}</td>
                                    <td style="width:100px;">
                                        <div class="btn-group" role="group" aria-label="...">
                                            <form action="{{route('system-messages.delete', ['id' => $message->id])}}" method="post">
                                                {{csrf_field()}}
                                                <a type="button"
                                                   class="btn btn-default"
                                                   title="Edit"
                                                   href="{{route('system-messages.edit', ['id' => $message->id])}}"
                                                >
                                                    <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                                                </a>
                                                <button class="btn btn-default" title="Delete" type="submit">
                                                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                                </button>


                                            </form>

                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                            <h4>Message list is empty.</h4>
                        @endif
                        {!! $messages->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection