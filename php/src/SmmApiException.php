<?php

declare(strict_types=1);

namespace EvaPanel\SmmApi;

/** The API could not be reached, or it answered {"error": "..."}. */
final class SmmApiException extends \RuntimeException {}
