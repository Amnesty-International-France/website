const SALESFORCE_CALLS_ENDPOINT = '/wp-json/aif-e2e/v1/salesforce-calls';

// Backed by aif-e2e-support.php's pre_http_request mock: every outbound
// Salesforce call (from includes/salesforce/data.php) is recorded there and
// exposed here so a spec can assert a real Salesforce call was triggered,
// without ever making a real network call.
//
// testId must be the same value as the `salesforceTestId` fixture (see
// fixtures.mjs) used by the rest of the test - it's how aif-e2e-support.php
// namespaces the call log per test so concurrent tests never see each
// other's calls. The `request` fixture doesn't inherit the page context's
// extra headers, so it's sent explicitly here.

export const resetSalesforceCalls = async (request, testId) => {
  await request.delete(SALESFORCE_CALLS_ENDPOINT, {
    headers: { 'X-AIF-E2E-Test-Id': testId },
  });
};

export const getSalesforceCalls = async (request, testId) => {
  const response = await request.get(SALESFORCE_CALLS_ENDPOINT, {
    headers: { 'X-AIF-E2E-Test-Id': testId },
  });
  return response.json();
};
