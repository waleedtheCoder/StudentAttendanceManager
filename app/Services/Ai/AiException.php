<?php

namespace App\Services\Ai;

use RuntimeException;

/** An AI request failed; the message is safe to show to the user. */
class AiException extends RuntimeException {}
