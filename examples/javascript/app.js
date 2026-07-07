/**
 * OTPAP browser Hello World example.
 */

async function runHelloWorld() {
  const pageResponse = await fetch('/page');
  const pageData = await pageResponse.json();
  const token = pageData.token;

  const apiResponse = await fetch('/api/hello', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-OTPAP-Token': JSON.stringify(token)
    },
    body: JSON.stringify({ message: 'Hello World' })
  });

  const output = await apiResponse.json();
  document.getElementById('output').textContent = JSON.stringify(output, null, 2);
}

document.getElementById('run').addEventListener('click', () => {
  runHelloWorld().catch((error) => {
    document.getElementById('output').textContent = error.stack || String(error);
  });
});
