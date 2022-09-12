
function marketHistoryAsJsonUrl() {
    return '<?php echo BotArenaWeb\marketHistoryAsJsonUrl(BotArenaWeb\botArenaId(),BotArenaWeb\traderId()); ?>';
}

function traderHistoryAsJsonUrl() {
    return '<?php echo BotArenaWeb\marketHistoryAsJsonUrl(BotArenaWeb\botArenaId(),BotArenaWeb\traderId()); ?>';
}
function viewRefreshMillis() {
    return '<?php echo BotArenaWeb\jsViewRefreshMillis(); ?>';
}