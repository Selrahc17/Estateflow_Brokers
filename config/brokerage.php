<?php

return [

    /*
    | Reported RA 9646 ratio of one licensed broker to 20 accredited salespersons.
    | Kept configurable until it is confirmed with the PRC.
    */
    'max_agents_per_broker' => (int) env('MAX_AGENTS_PER_BROKER', 20),

];
