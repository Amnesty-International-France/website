const SALESFORCE_CALLS_ENDPOINT = '/wp-json/aif-e2e/v1/salesforce-calls';

// Backed by aif-e2e-support.php's pre_http_request mock: every outbound
// Salesforce call is recorded and exposed here so a spec can assert one was
// triggered, without a real network call. testId must be the test's
// `salesforceTestId` fixture (see fixtures.mjs for why).

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
