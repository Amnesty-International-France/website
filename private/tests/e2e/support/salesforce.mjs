const SALESFORCE_CALLS_ENDPOINT = '/wp-json/aif-e2e/v1/salesforce-calls';

// Backed by aif-e2e-support.php's pre_http_request mock: every outbound
// Salesforce call (from includes/salesforce/data.php) is recorded there and
// exposed here so a spec can assert a real Salesforce call was triggered,
// without ever making a real network call.

export const resetSalesforceCalls = async (request) => {
  await request.delete(SALESFORCE_CALLS_ENDPOINT);
};

export const getSalesforceCalls = async (request) => {
  const response = await request.get(SALESFORCE_CALLS_ENDPOINT);
  return response.json();
};
