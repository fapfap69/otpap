"""
FastAPI Hello World API protected by OTPAP.
"""

from fastapi import FastAPI, Header, HTTPException, Request

from otpap import create_token, validate_token

app = FastAPI()
secret = "python-hello-world-secret"
replay_store: set[str] = set()
session = {
    "applicationId": "hello-world-app",
    "sessionId": "sess_python_001",
    "userId": "user_hello",
    "pageId": "page_hello",
    "apiId": "hello.world",
    "httpMethod": "POST",
    "sequenceNumber": 1,
}


@app.get("/page")
async def page():
    """Returns the page payload and an OTPAP token."""
    token = create_token({**session, "body": '{"message":"Hello World"}'}, secret, 60)
    return {"page": "Hello World", "token": token, "note": "POST the same body to /api/hello."}


@app.post("/api/hello")
async def hello_world(request: Request, x_otpap_token: str = Header(default="")):
    """Validates the OTPAP token and responds with Hello World."""
    if not x_otpap_token:
        raise HTTPException(status_code=400, detail={"valid": False, "code": "OTPAP-1001", "message": "Missing token."})

    body = await request.body()
    token = request.app.state.json_loader(x_otpap_token)
    result = validate_token(token, {**session, "body": body.decode("utf-8")}, secret, replay_store)
    if not result["valid"]:
        raise HTTPException(status_code=409, detail=result)

    return {"valid": True, "code": result["code"], "message": "Hello World", "consumed": True}


app.state.json_loader = lambda token: __import__("json").loads(token)
