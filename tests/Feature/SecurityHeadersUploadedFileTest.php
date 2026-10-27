<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\TestCase;

/**
 * SecurityHeaders runs after the controller, and must not read the request's files there.
 *
 * The flyer parse takes a FormRequest, a copy of the request with its own file cache, and
 * GeminiUtils::parseEvent() moves the upload out of /tmp. Asking the ORIGINAL request has('embed')
 * afterwards converted the upload for the first time, the temp file was gone, and the guest's
 * parse came back a 500 (EVENTSCHEDULE-PHP-4K). It hid behind CaptureUtmParameters, which converts
 * the files early unless the visitor's session already holds UTM params.
 */
class SecurityHeadersUploadedFileTest extends TestCase
{
    private function requestWithUpload(array $body, ?string $routeName): array
    {
        $path = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, 'flyer');

        $request = Request::create('https://sub.eventschedule.test/guest-parse', 'POST', $body, [], [
            'details_image' => new UploadedFile($path, 'flyer.jpg', 'image/jpeg', null, true),
        ]);

        if ($routeName) {
            $route = (new Route('POST', 'guest-parse', []))->name($routeName);
            $request->setRouteResolver(fn () => $route);
        }

        return [$request, $path];
    }

    private function handleMovingTheUpload(Request $request, string $path)
    {
        return (new SecurityHeaders)->handle($request, function () use ($path) {
            // What move_uploaded_file() does to the temp file inside the controller.
            unlink($path);

            return new Response('ok');
        });
    }

    public function test_an_upload_moved_by_the_controller_does_not_break_the_response(): void
    {
        [$request, $path] = $this->requestWithUpload(['event_details' => 'x'], 'event.guest_parse');

        $response = $this->handleMovingTheUpload($request, $path);

        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
    }

    public function test_embed_in_the_body_still_allows_framing_on_an_embeddable_route(): void
    {
        [$request, $path] = $this->requestWithUpload(['embed' => 'true'], 'event.checkout');

        $response = $this->handleMovingTheUpload($request, $path);

        $this->assertFalse($response->headers->has('X-Frame-Options'));
    }
}
