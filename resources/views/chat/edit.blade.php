@extends('layouts.app')
@section('content')
    <div class='row'>
        <div class="col-lg-4 col-lg-offset-4">
            <div class="panel panel-default">
                <div class="panel-heading">Edit Chat keyword</div>
                    <div class="panel-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(session('success'))
                            <div class="alert alert-success" role="alert">
                                {{session('success')}}
                            </div>
                        @endif
                        <form class="form-horizontal" method="post" role="form" action="{{ url('chat/'.$chat->id) }}">
                            <input type="hidden" name="_method" value="PUT">
                            <fieldset>
                            {!! csrf_field() !!}

                                <div class="form-group">
                                    <label for="keyword" class="col-md-4 control-label">Keyword</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="keyword" value="{{ $chat->keyword }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="reply" class="col-md-4 control-label">Reply</label>
                                    <div class="col-md-6">
                                        <textarea class="form-control" name="reply" required>{{ $chat->reply }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="disable" class="col-md-4 control-label">Disable</label>
                                    <div class="col-md-6">
                                        <input type="checkbox" class="form-control" name="disable" @if ($chat->disable) checked @endif>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="add_role_btn"></label>
                                    <div class="col-md-4">
                                        <button name="btn" class="btn btn-primary" type="submit">Edit</button>
                                    </div>
                                </div>

                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection