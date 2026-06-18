@if($trend === 'up')
    <span class="trend-up" title="Kinerja Meningkat">&uarr;</span>
@elseif($trend === 'down')
    <span class="trend-down" title="Kinerja Menurun">&darr;</span>
@elseif($trend === 'stable')
    <span class="trend-stable" title="Kinerja Stabil">&rarr;</span>
@else
    <span class="trend-unknown" title="Data Tidak Cukup">?</span>
@endif
