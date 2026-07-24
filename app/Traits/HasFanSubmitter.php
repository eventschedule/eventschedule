<?php

namespace App\Traits;

use App\Utils\UrlUtils;

/**
 * Shared submitter resolution for fan content (comments, photos, videos).
 *
 * A row is authored either by a signed-in user (user_id) or by a guest who supplied a
 * name and email on the submission form (guest_name / guest_email). Display surfaces
 * should always go through submitterName() rather than reaching for the relation.
 */
trait HasFanSubmitter
{
    /**
     * Name to show alongside the submission. Never returns an email.
     */
    public function submitterName(): string
    {
        $name = $this->user?->name ?: $this->guest_name;

        return trim((string) $name) !== '' ? $name : __('messages.user');
    }

    /**
     * Submitter's email, for owner-facing surfaces only (the AP moderation queue).
     * Must never be rendered on a guest/public page.
     */
    public function submitterEmail(): ?string
    {
        return $this->user?->email ?: $this->guest_email;
    }

    /**
     * Whether this row was submitted without an account.
     */
    public function isGuestSubmission(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Shared API shape for the fan-content endpoint.
     *
     * Deliberately carries no email: the submitter's address is for the schedule's own
     * moderation queue, not for anything an integration republishes. Each model supplies
     * its own type tag and payload fields.
     */
    public function toApiData()
    {
        $data = new \stdClass;

        $data->id = UrlUtils::encodeId($this->id);
        $data->type = $this->fanContentType();
        $data->event_id = UrlUtils::encodeId($this->event_id);
        $data->event_name = $this->event?->name;
        $data->event_part_id = $this->event_part_id ? UrlUtils::encodeId($this->event_part_id) : null;
        $data->event_date = $this->event_date;
        $data->submitted_by = $this->submitterName();
        $data->is_guest_submission = $this->isGuestSubmission();
        $data->is_approved = (bool) $this->is_approved;

        foreach ($this->fanContentApiFields() as $key => $value) {
            $data->{$key} = $value;
        }

        $data->created_at = $this->created_at?->toIso8601String();

        return $data;
    }
}
