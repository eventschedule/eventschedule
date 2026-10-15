<?php

namespace App\Services\Payments\Razorpay;

/**
 * A Razorpay refund that was never asked for: the list of the payment's existing refunds, which
 * RazorpayGateway::refund() reads first to stay idempotent, could not be read. Only a GET left this
 * machine, so nothing can have moved, and RazorpayGateway::classifyRefundFailure() reports it as a
 * definite failure the owner can simply try again, not as an unknown outcome to park.
 */
class RazorpayRefundNotSent extends \RuntimeException {}
