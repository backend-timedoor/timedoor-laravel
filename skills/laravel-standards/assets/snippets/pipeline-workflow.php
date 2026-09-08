<?php

// Curated snippet: ordered stages transforming one typed workflow state.
// Use only for multi-stage workflows; direct calls fit trivial flows.

use Illuminate\Pipeline\Pipeline;

$state = app(Pipeline::class)
    ->send($state)
    ->via('process')
    ->through([
        ValidateStage::class,
        AuthorizeStage::class,
        PersistStage::class,
    ])
    ->then(static fn (WorkflowState $state): WorkflowState => $state);
