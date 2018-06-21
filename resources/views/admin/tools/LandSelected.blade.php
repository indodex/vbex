
<div class="btn-group pull-right open" style="margin-right: 10px">
    <a class="btn btn-sm btn-twitter"><i class="fa fa-language"></i> 语言</a>
    <button type="button" class="btn btn-sm btn-twitter dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu" role="menu">
    	@foreach($options as $option => $label)
        @if ($option ==  'cn')
            <li><a href="{{ $url }}" >{{$label}}</a></li>
        @else
            <li><a href="{{ $url }}_{{ $option }}" >{{$label}}</a></li>
        @endif
    	
<!--     <label class="btn btn-default btn-sm {{ \Request::get('gender', 'all') == $option ? 'active' : '' }}">
        <input type="radio" class="user-gender" value="{{ $option }}">{{$label}}
    </label> -->
    @endforeach
    </ul>
</div>