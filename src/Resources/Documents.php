<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use Szabrob\LaravelBillingo\Data\Documents\CancelDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CreateDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CreateFromProformaData;
use Szabrob\LaravelBillingo\Data\Documents\CreateModificationDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CreateReceiptData;
use Szabrob\LaravelBillingo\Data\Documents\DocumentData;
use Szabrob\LaravelBillingo\Data\Documents\DocumentFilterData;
use Szabrob\LaravelBillingo\Data\Documents\DocumentRemindersData;
use Szabrob\LaravelBillingo\Data\Documents\OnlineSzamlaStatusData;
use Szabrob\LaravelBillingo\Data\Documents\PaymentData;
use Szabrob\LaravelBillingo\Data\Documents\PublicUrlData;
use Szabrob\LaravelBillingo\Data\Documents\SendDocumentData;
use Szabrob\LaravelBillingo\Exceptions\DocumentNotReadyException;
use Szabrob\LaravelBillingo\Support\PaginatedResponse;

final class Documents extends Resource
{
    /**
     * @return PaginatedResponse<DocumentData>
     */
    public function list(?DocumentFilterData $filter = null, int $page = 1, int $perPage = 25): PaginatedResponse
    {
        $response = $this->connector->get('documents', [
            ...($filter?->toPayload() ?? []),
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return PaginatedResponse::fromArray($this->json($response), DocumentData::class);
    }

    /**
     * @return LazyCollection<int, DocumentData>
     */
    public function lazy(?DocumentFilterData $filter = null, int $perPage = 100): LazyCollection
    {
        return $this->lazily(fn (int $page): PaginatedResponse => $this->list($filter, $page, $perPage));
    }

    public function get(int $id): DocumentData
    {
        return $this->document($this->connector->get("documents/{$id}"));
    }

    /**
     * Find a document by the vendor ID given when it was created.
     */
    public function findByVendorId(string $vendorId): DocumentData
    {
        return $this->document($this->connector->get('documents/vendor/'.rawurlencode($vendorId)));
    }

    public function create(CreateDocumentData $document): DocumentData
    {
        return $this->document($this->connector->post('documents', $document->toPayload()));
    }

    /**
     * Turn an existing draft into a real document.
     */
    public function createFromDraft(int $draftId, CreateDocumentData $document): DocumentData
    {
        return $this->document($this->connector->put("documents/{$draftId}", $document->toPayload()));
    }

    public function createReceipt(CreateReceiptData $receipt): DocumentData
    {
        return $this->document($this->connector->post('documents/receipt', $receipt->toPayload()));
    }

    /**
     * Turn an existing receipt draft into a real receipt.
     */
    public function createReceiptFromDraft(int $draftId, CreateReceiptData $receipt): DocumentData
    {
        return $this->document($this->connector->put("documents/receipt/{$draftId}", $receipt->toPayload()));
    }

    public function createFromProforma(int $proformaId, ?CreateFromProformaData $settings = null): DocumentData
    {
        return $this->document($this->connector->post(
            "documents/{$proformaId}/create-from-proforma",
            $settings?->toPayload() ?? [],
        ));
    }

    public function createModification(int $id, CreateModificationDocumentData $modification): DocumentData
    {
        return $this->document($this->connector->post(
            "documents/{$id}/create-modification-document",
            $modification->toPayload(),
        ));
    }

    public function cancel(int $id, ?CancelDocumentData $cancellation = null): DocumentData
    {
        return $this->document($this->connector->post("documents/{$id}/cancel", $cancellation?->toPayload() ?? []));
    }

    public function copy(int $id): DocumentData
    {
        return $this->document($this->connector->post("documents/{$id}/copy"));
    }

    /**
     * Delete a draft document.
     */
    public function delete(int $id): void
    {
        $this->connector->delete("documents/{$id}");
    }

    public function archive(int $id): void
    {
        $this->connector->put("documents/{$id}/archive");
    }

    /**
     * Send the document to the given e-mail addresses, or to the partner when none given.
     *
     * @param  list<string>  $emails
     * @return list<string> The addresses the document was sent to.
     */
    public function send(int $id, array $emails = []): array
    {
        $payload = (new SendDocumentData(emails: $emails === [] ? null : $emails))->toPayload();
        $sent = SendDocumentData::from($this->json($this->connector->post("documents/{$id}/send", $payload)));

        return array_values($sent->emails ?? []);
    }

    /**
     * Download the document PDF as raw contents.
     *
     * @throws DocumentNotReadyException when Billingo has not generated the PDF yet.
     */
    public function download(int $id): string
    {
        $response = $this->connector->get("documents/{$id}/download");

        if ($response->status() === 202) {
            throw new DocumentNotReadyException($id, $response);
        }

        return $response->body();
    }

    /**
     * Download the receipt formatted for POS printers (58 or 80 mm wide paper).
     */
    public function posPrint(int $id, int $size = 80): string
    {
        if (! in_array($size, [58, 80], true)) {
            throw new InvalidArgumentException('The POS print size must be 58 or 80.');
        }

        return $this->connector->get("documents/{$id}/print/pos", ['size' => $size])->body();
    }

    public function publicUrl(int $id): string
    {
        return (string) PublicUrlData::from($this->json($this->connector->get("documents/{$id}/public-url")))->publicUrl;
    }

    public function onlineSzamlaStatus(int $id): OnlineSzamlaStatusData
    {
        return OnlineSzamlaStatusData::from($this->json($this->connector->get("documents/{$id}/online-szamla")));
    }

    public function reminders(int $id): DocumentRemindersData
    {
        return DocumentRemindersData::from($this->json($this->connector->get("documents/{$id}/reminders")));
    }

    /**
     * @return Collection<int, PaymentData>
     */
    public function payments(int $id): Collection
    {
        return $this->paymentCollection($this->connector->get("documents/{$id}/payments"));
    }

    /**
     * Replace the payment history of the document.
     *
     * @param  iterable<PaymentData>  $payments
     * @return Collection<int, PaymentData>
     */
    public function updatePayments(int $id, iterable $payments): Collection
    {
        $payload = [];

        foreach ($payments as $payment) {
            $payload[] = $payment->toPayload();
        }

        return $this->paymentCollection($this->connector->put("documents/{$id}/payments", $payload));
    }

    /**
     * @return Collection<int, PaymentData>
     */
    public function deletePayments(int $id): Collection
    {
        return $this->paymentCollection($this->connector->delete("documents/{$id}/payments"));
    }

    private function document(Response $response): DocumentData
    {
        return DocumentData::from($this->json($response));
    }

    /**
     * @return Collection<int, PaymentData>
     */
    private function paymentCollection(Response $response): Collection
    {
        $payments = $response->json();

        /** @var Collection<int, PaymentData> */
        return PaymentData::collect(is_array($payments) ? array_values($payments) : [], Collection::class);
    }
}
