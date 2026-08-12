const JETPACK_EFFECTS_ENDPOINT = '/wp-json/aif-e2e/v1/jetpack-effects';

export const resetJetpackEffects = async (request, testId) => {
  await request.delete(JETPACK_EFFECTS_ENDPOINT, {
    headers: { 'X-AIF-E2E-Test-Id': testId },
  });
};

export const getJetpackEffects = async (request, testId) => {
  const response = await request.get(JETPACK_EFFECTS_ENDPOINT, {
    headers: { 'X-AIF-E2E-Test-Id': testId },
  });

  return response.json();
};
