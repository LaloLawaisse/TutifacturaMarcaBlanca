<!-- Left side column. contains the logo and sidebar -->
<aside class="side-bar tw-relative tw-hidden tw-h-full tw-bg-white tw-w-64 xl:tw-w-64 lg:tw-flex lg:tw-flex-col tw-shrink-0">

    <!-- sidebar: style can be found in sidebar.less -->

    {{-- <a href="{{route('home')}}" class="logo">
		<span class="logo-lg">{{ Session::get('business.name') }}</span>
	</a> --}}

    
    <a href="{{ route('home') }}"
       class="tw-flex tw-items-center tw-justify-center tw-w-full tw-border-r tw-h-15 tw-shrink-0 tw-border-primary-500/30"
       style="background-color: #005a5a;">
        <img src="{{ asset('img/logoSidebar.jpg') }}" alt="Logo" class="tw-h-15 tw-object-contain" />
    </a>

    <!-- Sidebar Menu -->
    {!! Menu::render('admin-sidebar-menu', 'adminltecustom') !!}

    <!-- /.sidebar-menu -->
    <!-- /.sidebar -->
</aside>
