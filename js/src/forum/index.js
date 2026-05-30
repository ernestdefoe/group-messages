import app from 'flarum/forum/app';

// Group Messages — forum frontend. UI (group compose modal, participant
// management, group-aware dialog rendering, read receipts) is wired up in
// later phases. This initializer keeps the bundle valid in the meantime.
app.initializers.add('ernestdefoe-group-messages', () => {
  // Phase 4: register the group compose flow and dialog overrides here.
});
