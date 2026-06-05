<?php
namespace service\fnc;
class message extends \service\baseExtend {

    /**
     * pushNotifikacia — previously sent Pushover notifications using the ex-developer's
     * personal Pushover account (which he was using to spy on user logins).
     * This has been disabled. To re-enable notifications, configure a new Pushover/ntfy/etc.
     * account and implement it here.
     */
    public function pushNotifikacia(){
        // Removed: was sending to developer's personal Pushover account.
        return $this->output("disabled", true);
    }

    public function socketNotifikacia(){
        // Removed: was sending to developer's personal Pushover account.
        return $this->output("disabled", true);
    }

}
