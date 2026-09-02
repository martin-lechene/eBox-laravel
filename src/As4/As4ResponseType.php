<?php

namespace Ebox\Enterprise\As4;

/**
 * Classifies what came back from the receiving AS4 MSH.
 */
enum As4ResponseType: string
{
    case RECEIPT = 'receipt';
    case ERROR = 'error';
    case FAULT = 'fault';
    case NONE = 'none';
}
