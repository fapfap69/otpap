// Go Hello World API protected by OTPAP.
package main

import (
	"encoding/json"
	"net/http"
	"time"
)

var replayStore = map[string]bool{}

const secret = "go-hello-world-secret"

var session = map[string]any{
	"applicationId":  "hello-world-app",
	"sessionId":      "sess_go_001",
	"userId":         "user_hello",
	"pageId":         "page_hello",
	"apiId":          "hello.world",
	"httpMethod":     "POST",
	"sequenceNumber": int64(1),
}

func pageHandler(writer http.ResponseWriter, _ *http.Request) {
	// The server issues a one-time token for the Hello World request.
	requestContext := map[string]any{
		"applicationId":  session["applicationId"],
		"sessionId":      session["sessionId"],
		"userId":         session["userId"],
		"pageId":         session["pageId"],
		"apiId":          session["apiId"],
		"httpMethod":     session["httpMethod"],
		"sequenceNumber": session["sequenceNumber"],
		"timestamp":      time.Now().Unix(),
		"body":           `{"message":"Hello World"}`,
	}
	token := createToken(requestContext, secret, 60)
	writer.Header().Set("Content-Type", "application/json")
	_ = json.NewEncoder(writer).Encode(map[string]any{"page": "Hello World", "token": token})
}

func helloHandler(writer http.ResponseWriter, request *http.Request) {
	// The request must carry the same body that was used to mint the token.
	tokenJSON := request.Header.Get("X-OTPAP-Token")
	if tokenJSON == "" {
		http.Error(writer, "missing token", http.StatusBadRequest)
		return
	}

	var token map[string]any
	_ = json.Unmarshal([]byte(tokenJSON), &token)
	body := `{"message":"Hello World"}`
	requestContext := map[string]any{
		"body":          body,
		"applicationId": session["applicationId"],
		"sessionId":     session["sessionId"],
		"userId":        session["userId"],
		"pageId":        session["pageId"],
		"apiId":         session["apiId"],
		"httpMethod":    session["httpMethod"],
		"timestamp":     time.Now().Unix(),
	}
	result := validateToken(token, requestContext, secret, replayStore)
	writer.Header().Set("Content-Type", "application/json")
	if valid, _ := result["valid"].(bool); !valid {
		writer.WriteHeader(http.StatusConflict)
		_ = json.NewEncoder(writer).Encode(result)
		return
	}
	_ = json.NewEncoder(writer).Encode(map[string]any{"valid": true, "code": "OTPAP-0000", "message": "Hello World", "consumed": true})
}

func main() {
	http.HandleFunc("/page", pageHandler)
	http.HandleFunc("/api/hello", helloHandler)
	_ = http.ListenAndServe(":8080", nil)
}
