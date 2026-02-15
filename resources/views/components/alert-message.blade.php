@if ($message = Session::get('success'))
    <div style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 14px;">
        {{ $message }}
    </div>
@endif

@if ($message = Session::get('error'))
    <div style="background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 14px;">
        {{ $message }}
    </div>
@endif

@if ($message = Session::get('warning'))
    <div style="background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 14px;">
        {{ $message }}
    </div>
@endif

@if ($message = Session::get('info'))
    <div style="background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 14px;">
        {{ $message }}
    </div>
@endif
