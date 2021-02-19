<aside class="col-sm-3 hidden-xs" id="column-right">
    <div class="list-group">
        <a class="list-group-item<?=$ActivePage == 'My Account' ? ' active' : ''?>" href="/account/">My Account</a>
        <a class="list-group-item<?=$ActivePage == 'My Profile' ? ' active' : ''?>" href="/account/profile/">My Profile</a>
        <a class="list-group-item<?=$ActivePage == 'Password' ? ' active' : ''?>" href="/account/change-password/">Password</a>
        <a class="list-group-item<?=$ActivePage == 'Address Book' ? ' active' : ''?>" href="/account/addresses/">Address Book</a>
        <a class="list-group-item<?=$ActivePage == 'Order History' ? ' active' : ''?>" href="/account/orders/">Order History</a>
        <a class="list-group-item<?=$ActivePage == 'Reviews' ? ' active' : ''?>" href="/account/reviews/">Reviews</a>
    </div>
</aside>