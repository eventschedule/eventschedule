<?php

namespace Tests\Unit;

use App\Services\Payments\Payfast\PayfastSignature;
use PHPUnit\Framework\TestCase;

/**
 * The MD5 signature is the whole of Payfast's authentication, in both directions, and every part of
 * how the string is built is significant. These pin the parts that are easy to get subtly wrong.
 */
class PayfastSignatureTest extends TestCase
{
    public function test_the_field_order_is_payfasts_and_not_the_arrays(): void
    {
        // Payfast requires its documented order, and http_build_query() preserves insertion order, so
        // a signature built from the caller's array order is wrong even with identical values. Two
        // differently-ordered inputs must therefore produce the SAME signature.
        $a = [
            'merchant_id' => '10000100',
            'merchant_key' => '46f0cd694581a',
            'amount' => '100.00',
            'item_name' => 'Ticket',
        ];
        $b = [
            'item_name' => 'Ticket',
            'amount' => '100.00',
            'merchant_key' => '46f0cd694581a',
            'merchant_id' => '10000100',
        ];

        $this->assertSame(
            PayfastSignature::sign($a, null),
            PayfastSignature::sign($b, null),
        );

        // And the order really is Payfast's, not alphabetical or insertion order.
        $expected = md5(http_build_query([
            'merchant_id' => '10000100',
            'merchant_key' => '46f0cd694581a',
            'amount' => '100.00',
            'item_name' => 'Ticket',
        ]));

        $this->assertSame($expected, PayfastSignature::sign($a, null));
    }

    public function test_empty_values_are_dropped_rather_than_signed_as_blank(): void
    {
        // Payfast excludes blank fields from the signature. An included-but-empty field hashes
        // differently from an omitted one, so this is the difference between working and not.
        $withBlanks = [
            'merchant_id' => '10000100',
            'name_first' => '',
            'name_last' => null,
            'amount' => '100.00',
        ];

        $this->assertSame(
            md5(http_build_query(['merchant_id' => '10000100', 'amount' => '100.00'])),
            PayfastSignature::sign($withBlanks, null),
        );
    }

    public function test_unknown_fields_are_never_signed(): void
    {
        // Only fields Payfast knows about take part. Signing an extra one would make our signature
        // disagree with theirs for every payment.
        $this->assertSame(
            PayfastSignature::sign(['merchant_id' => '10000100'], null),
            PayfastSignature::sign(['merchant_id' => '10000100', 'not_a_payfast_field' => 'x'], null),
        );
    }

    public function test_the_passphrase_changes_the_signature_and_is_appended_last(): void
    {
        $fields = ['merchant_id' => '10000100', 'amount' => '100.00'];

        $unsigned = PayfastSignature::sign($fields, null);
        $signed = PayfastSignature::sign($fields, 'test-passphrase');

        $this->assertNotSame($unsigned, $signed);

        $this->assertSame(
            md5(http_build_query($fields + ['passphrase' => 'test-passphrase'])),
            $signed,
        );
    }

    public function test_an_itn_with_empty_fields_verifies_from_the_raw_body(): void
    {
        // The trap this whole method exists for. Payfast sends plenty of empty fields, and Laravel's
        // ConvertEmptyStringsToNull turns them into null while http_build_query() drops nulls - so a
        // signature rebuilt from the parsed request is missing keys Payfast included, and every real
        // payment fails to verify. Reading the raw body is what makes this pass.
        $passphrase = 'test-passphrase';
        $body = 'm_payment_id=abc&pf_payment_id=2579&payment_status=COMPLETE&item_name=Ticket'
            .'&amount_gross=100.00&custom_str1=&custom_str2=&name_first=&email_address=';

        $signature = md5($body.'&passphrase='.urlencode($passphrase));

        $this->assertTrue(PayfastSignature::verifyItn($body.'&signature='.$signature, $passphrase));
    }

    public function test_a_tampered_itn_does_not_verify(): void
    {
        $passphrase = 'test-passphrase';
        $body = 'm_payment_id=abc&pf_payment_id=2579&payment_status=COMPLETE&amount_gross=100.00';
        $signature = md5($body.'&passphrase='.urlencode($passphrase));

        // The amount is raised after signing, which is the attack the signature is there to stop.
        $tampered = 'm_payment_id=abc&pf_payment_id=2579&payment_status=COMPLETE&amount_gross=1.00'
            .'&signature='.$signature;

        $this->assertFalse(PayfastSignature::verifyItn($tampered, $passphrase));
    }

    public function test_a_signature_from_a_different_passphrase_does_not_verify(): void
    {
        $body = 'm_payment_id=abc&pf_payment_id=2579&payment_status=COMPLETE&amount_gross=100.00';
        $signature = md5($body.'&passphrase='.urlencode('someone-elses-passphrase'));

        $this->assertFalse(PayfastSignature::verifyItn($body.'&signature='.$signature, 'test-passphrase'));
    }

    public function test_an_itn_with_no_signature_does_not_verify(): void
    {
        // Fail closed. An unsigned body must never be treated as authentic just because there is
        // nothing to compare against.
        $this->assertFalse(PayfastSignature::verifyItn('payment_status=COMPLETE', 'test-passphrase'));
        $this->assertFalse(PayfastSignature::verifyItn('payment_status=COMPLETE&signature=', 'test-passphrase'));
    }

    public function test_itn_field_order_is_taken_from_the_body_not_our_own_order(): void
    {
        // Inbound uses THEIR order, which is the opposite rule to sign(). Reordering the body must
        // therefore change the expected signature.
        $passphrase = 'test-passphrase';
        $one = 'a=1&b=2';
        $two = 'b=2&a=1';

        $signatureForOne = md5($one.'&passphrase='.urlencode($passphrase));

        $this->assertTrue(PayfastSignature::verifyItn($one.'&signature='.$signatureForOne, $passphrase));
        $this->assertFalse(PayfastSignature::verifyItn($two.'&signature='.$signatureForOne, $passphrase));
    }
}
