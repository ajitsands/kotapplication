package com.sandslab.kotadmin;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.os.Bundle;
import android.view.View;
import android.view.WindowManager;
import android.webkit.CookieManager;
import android.webkit.JsResult;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.Toast;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

public class MainActivity extends Activity {
    private WebView webView;
    private SwipeRefreshLayout swipeRefresh;
    private SharedPreferences prefs;
    private static final String PREF_SERVER_URL = "admin_server_url";
    private static final String DEFAULT_URL = "https://kot.sandslab.com/admin-app";

    @SuppressLint("SetJavaScriptEnabled")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        prefs = getSharedPreferences("KOT_ADMIN_PREFS", Context.MODE_PRIVATE);

        // Keep screen awake
        getWindow().addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON);

        // Layout container with SwipeRefreshLayout
        swipeRefresh = new SwipeRefreshLayout(this);
        webView = new WebView(this);
        swipeRefresh.addView(webView);
        setContentView(swipeRefresh);

        // Enable Cookies & Persistence
        CookieManager.getInstance().setAcceptCookie(true);
        CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true);

        // Configure WebView
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setDatabaseEnabled(true);
        settings.setAllowFileAccess(true);
        settings.setLoadsImagesAutomatically(true);
        settings.setUseWideViewPort(true);
        settings.setLoadWithOverviewMode(true);
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_ALWAYS_ALLOW);

        // Swipe refresh handler
        swipeRefresh.setOnRefreshListener(() -> {
            webView.reload();
        });

        webView.addJavascriptInterface(new Object() {
            @android.webkit.JavascriptInterface
            public void openSettings() {
                runOnUiThread(() -> showServerUrlDialog());
            }
        }, "AdminAppBridge");

        webView.setWebViewClient(new WebViewClient() {
            @Override
            public void onPageStarted(WebView view, String url, Bitmap favicon) {
                super.onPageStarted(view, url, favicon);
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                swipeRefresh.setRefreshing(false);
            }

            @Override
            public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
                super.onReceivedError(view, request, error);
                swipeRefresh.setRefreshing(false);
                
                if (request.isForMainFrame()) {
                    String failedUrl = request.getUrl().toString();
                    showErrorHtml(view, failedUrl, "Connection Error", "Cannot reach the server. Please check your domain name or internet.");
                }
            }

            @Override
            public void onReceivedHttpError(WebView view, WebResourceRequest request, android.webkit.WebResourceResponse errorResponse) {
                super.onReceivedHttpError(view, request, errorResponse);
                swipeRefresh.setRefreshing(false);

                if (request.isForMainFrame() && errorResponse != null && errorResponse.getStatusCode() >= 400) {
                    String failedUrl = request.getUrl().toString();
                    showErrorHtml(view, failedUrl, "HTTP " + errorResponse.getStatusCode() + " Error", "The page on your server was not found (404). Please reconfigure your domain name.");
                }
            }

            private void showErrorHtml(WebView view, String failedUrl, String title, String subtitle) {
                String html = "<!DOCTYPE html><html><head><meta name='viewport' content='width=device-width, initial-scale=1.0'></head><body style='background:#0b0f19;color:#fff;font-family:-apple-system,BlinkMacSystemFont,sans-serif;text-align:center;padding:40px 20px;margin:0;'>"
                    + "<div style='font-size:52px;margin-bottom:12px;'>⚠️</div>"
                    + "<h2 style='font-size:22px;margin-bottom:8px;color:#f87171;'>" + title + "</h2>"
                    + "<p style='color:#cbd5e1;font-size:14px;line-height:1.5;margin-bottom:15px;'>" + subtitle + "</p>"
                    + "<p style='color:#64748b;font-size:12px;word-break:break-all;margin-bottom:25px;background:#1e293b;padding:8px 12px;border-radius:8px;'>" + failedUrl + "</p>"
                    + "<button onclick='AdminAppBridge.openSettings()' style='background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:none;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;box-shadow:0 4px 15px rgba(99,102,241,0.4);'>⚙️ Configure Server Domain</button>"
                    + "</body></html>";
                view.loadDataWithBaseURL(null, html, "text/html", "utf-8", null);
            }
        });

        webView.setWebChromeClient(new WebChromeClient() {
            @Override
            public boolean onJsAlert(WebView view, String url, String message, JsResult result) {
                return super.onJsAlert(view, url, message, result);
            }

            @Override
            public boolean onJsConfirm(WebView view, String url, String message, JsResult result) {
                return super.onJsConfirm(view, url, message, result);
            }
        });

        // If first launch without configured domain, ask user
        if (!prefs.contains(PREF_SERVER_URL)) {
            showServerUrlDialog();
        } else {
            String serverUrl = prefs.getString(PREF_SERVER_URL, "");
            if (serverUrl.isEmpty()) {
                showServerUrlDialog();
            } else {
                webView.loadUrl(serverUrl);
            }
        }
    }

    public void showServerUrlDialog() {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("👑 SaNDS Admin App Setup");
        builder.setMessage("Enter your Restaurant Domain or Subdomain (e.g. b1.restoflow.us):");

        final EditText input = new EditText(this);
        input.setHint("e.g. b1.restoflow.us");
        String currentUrl = prefs.getString(PREF_SERVER_URL, "");
        if (!currentUrl.isEmpty()) {
            input.setText(currentUrl.replace("https://", "").replace("http://", "").replace("/admin-app", ""));
            input.setSelection(input.getText().length());
        }
        
        LinearLayout container = new LinearLayout(this);
        container.setOrientation(LinearLayout.VERTICAL);
        container.setPadding(50, 20, 50, 10);
        container.addView(input);
        builder.setView(container);

        builder.setPositiveButton("Connect & Save", (dialog, which) -> {
            String domain = input.getText().toString().trim();
            if (!domain.isEmpty()) {
                domain = domain.replace("https://", "").replace("http://", "").replaceAll("/+$", "");
                String newUrl = "https://" + domain + "/admin-app";
                prefs.edit().putString(PREF_SERVER_URL, newUrl).apply();
                webView.loadUrl(newUrl);
                Toast.makeText(MainActivity.this, "Connecting to " + domain, Toast.LENGTH_SHORT).show();
            }
        });

        builder.setNegativeButton("Cancel", (dialog, which) -> {
            dialog.cancel();
            String savedUrl = prefs.getString(PREF_SERVER_URL, "");
            if (savedUrl.isEmpty() && webView.getUrl() == null) {
                showServerUrlDialog();
            }
        });
        builder.setCancelable(false);
        builder.show();
    }

    @Override
    public void onBackPressed() {
        if (webView != null && webView.canGoBack()) {
            webView.goBack();
        } else {
            new AlertDialog.Builder(this)
                .setTitle("Exit KOT Admin?")
                .setMessage("Do you want to close the Admin App?")
                .setPositiveButton("Exit", (dialog, which) -> finish())
                .setNegativeButton("Settings", (dialog, which) -> showServerUrlDialog())
                .setNeutralButton("Stay", null)
                .show();
        }
    }
}
