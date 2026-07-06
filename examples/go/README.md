# Go Example

```go
package main

import (
	"encoding/json"
	"net/http"
)

var replayStore = map[string]bool{}

func validateOTPAP(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		tokenJSON := r.Header.Get("X-OTPAP-Token")
		var token map[string]any
		_ = json.Unmarshal([]byte(tokenJSON), &token)

		// Replay detection and context validation should happen here.
		tokenID := "derived-token-id"
		if replayStore[tokenID] {
			w.WriteHeader(http.StatusConflict)
			_ = json.NewEncoder(w).Encode(map[string]any{"valid": false, "code": "OTPAP-1010"})
			return
		}

		replayStore[tokenID] = true
		next(w, r)
	}
}

func createOrder(w http.ResponseWriter, r *http.Request) {
	_ = json.NewEncoder(w).Encode(map[string]any{"valid": true, "code": "OTPAP-0000"})
}

func main() {
	http.HandleFunc("/api/orders/create", validateOTPAP(createOrder))
	_ = http.ListenAndServe(":8080", nil)
}
```
