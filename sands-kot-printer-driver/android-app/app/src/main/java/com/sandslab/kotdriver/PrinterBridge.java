package com.sandslab.kotdriver;

import android.app.Activity;
import android.util.Base64;
import android.util.Log;
import android.webkit.JavascriptInterface;
import android.webkit.WebView;
import org.json.JSONObject;
import java.io.OutputStream;
import java.net.InetSocketAddress;
import java.net.Socket;

/**
 * SaNDS Native ESC/POS Network Socket Print Bridge
 * Streams raw binary commands directly to any thermal network printer over Wi-Fi
 */
public class PrinterBridge {
    private static final String TAG = "SaNDSPrinterBridge";
    private Activity activity;
    private WebView webView;

    public PrinterBridge() {
    }

    public PrinterBridge(Activity activity, WebView webView) {
        this.activity = activity;
        this.webView = webView;
    }

    @JavascriptInterface
    public String printTcp(String ip, int port, String base64Data) {
        JSONObject result = new JSONObject();
        Socket socket = null;
        OutputStream outputStream = null;

        try {
            if (ip == null || ip.trim().isEmpty()) {
                ip = "192.168.8.101";
            }
            if (port <= 0) {
                port = 9100;
            }

            byte[] bytes = Base64.decode(base64Data, Base64.DEFAULT);

            socket = new Socket();
            // Connect to printer on local Wi-Fi with 3 second timeout
            socket.connect(new InetSocketAddress(ip.trim(), port), 3000);
            socket.setSoTimeout(3000);

            outputStream = socket.getOutputStream();
            outputStream.write(bytes);
            outputStream.flush();

            result.put("success", true);
            result.put("bytes", bytes.length);
            result.put("message", "Direct print transmitted to " + ip + ":" + port);
            Log.d(TAG, "Successfully printed " + bytes.length + " bytes to " + ip + ":" + port);

        } catch (Exception e) {
            Log.e(TAG, "Socket Print Error", e);
            try {
                result.put("success", false);
                result.put("error", e.getMessage() != null ? e.getMessage() : "Unable to connect to printer at " + ip + ":" + port);
            } catch (Exception ex) {
                // ignore
            }
        } finally {
            try {
                if (outputStream != null) outputStream.close();
                if (socket != null) socket.close();
            } catch (Exception e) {
                // ignore
            }
        }

        return result.toString();
    }

    @JavascriptInterface
    public void goBack() {
        if (activity != null && webView != null) {
            activity.runOnUiThread(() -> {
                if (webView.canGoBack()) {
                    webView.goBack();
                }
            });
        }
    }

    @JavascriptInterface
    public void closeWindow() {
        if (activity != null && webView != null) {
            activity.runOnUiThread(() -> {
                if (webView.canGoBack()) {
                    webView.goBack();
                } else {
                    activity.finish();
                }
            });
        }
    }

    @JavascriptInterface
    public void exitApp() {
        if (activity != null) {
            activity.runOnUiThread(() -> {
                activity.finishAffinity();
            });
        }
    }
}
