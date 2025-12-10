import Foundation

/// Helper exposed in the mobile event form to normalize dates before hitting the Events API.
/// Backend expects the `starts_at` payload to reflect the user's local wall time
/// (no automatic conversion to UTC), so we keep the formatter on `.current`.
struct EventFormView {
    var selectedDate: Date

    /// Formats the selected date into the `Y-m-d H:i:s` shape expected by the API
    /// without shifting the wall time to UTC.
    var apiDateString: String {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"
        formatter.timeZone = .current
        formatter.locale = Locale(identifier: "en_US_POSIX")
        return formatter.string(from: selectedDate)
    }
}
