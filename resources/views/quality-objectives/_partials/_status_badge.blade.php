@if($status === 'excellent')
    <span class="badge badge-excellent">Excellent</span>
@elseif($status === 'on_track')
    <span class="badge badge-on-track">On Track</span>
@elseif($status === 'at_risk')
    <span class="badge badge-at-risk">At Risk</span>
@elseif($status === 'off_track')
    <span class="badge badge-off-track">Off Track</span>
@elseif($status === 'draft')
    <span class="badge badge-draft">Draft</span>
@elseif($status === 'submitted')
    <span class="badge badge-submitted">Submitted</span>
@elseif($status === 'revision')
    <span class="badge badge-warning">Revision</span>
@elseif($status === 'pending_director')
    <span class="badge badge-review">Pending Director</span>
@elseif($status === 'active')
    <span class="badge badge-approved">Active</span>
@elseif($status === 'closed')
    <span class="badge badge-muted">Closed</span>
@elseif($status === 'rejected')
    <span class="badge badge-rejected">Rejected</span>
@elseif($status === 'renewed')
    <span class="badge badge-approved">Renewed</span>
@else
    <span class="badge badge-not-reported">Not Reported</span>
@endif
