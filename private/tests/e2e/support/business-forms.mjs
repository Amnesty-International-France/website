const BUSINESS_FORM_FIXTURES_ENDPOINT = '/wp-json/aif-e2e/v1/business-form-fixtures';
const CLH_STATE_ENDPOINT = '/wp-json/aif-e2e/v1/clh-state';

export const setupBusinessFormFixtures = async (request, testId) => {
  const response = await request.post(BUSINESS_FORM_FIXTURES_ENDPOINT, {
    headers: { 'X-AIF-E2E-Test-Id': testId },
  });

  return response.json();
};

export const getClhState = async (page) =>
  page.evaluate(async (endpoint) => {
    const response = await fetch(endpoint);
    return response.json();
  }, CLH_STATE_ENDPOINT);
