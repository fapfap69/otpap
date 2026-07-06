# Python FastAPI Example

```python
from fastapi import FastAPI, Header, HTTPException, Request

app = FastAPI()
replay_store = set()

@app.post("/api/orders/create")
async def create_order(request: Request, x_otpap_token: str = Header(...)):
    token = request.app.state.parser(x_otpap_token)  # Replace with your OTPAP parser.
    token_id = f"{token['ApplicationId']}:{token['SessionId']}:{token['PageId']}:{token['ApiId']}:{token['HttpMethod']}:{token['Nonce']}:{token['SequenceNumber']}"

    # Replay detection is mandatory.
    if token_id in replay_store:
        raise HTTPException(status_code=409, detail={"valid": False, "code": "OTPAP-1010"})

    body = await request.body()
    # Verify body hash, signature, page binding, API binding, and method binding here.
    replay_store.add(token_id)

    return {"valid": True, "code": "OTPAP-0000", "consumed": True}
```
