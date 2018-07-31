<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Event;

final class NativeQueryFromFileBuilderEvents
{
    /**
     * Called when filter's types are evaluated.
     *
     * Listeners have the opportunity to change the behavior of the query param values
     *
     * @Event("Micayael\NativeQueryFromFileBuilderBundle\Event\ProcessQueryParamsEvent")
     */
    const PROCESS_QUERY_PARAMS = 'native_query_from_file_builder.events.process_query_params';
}
