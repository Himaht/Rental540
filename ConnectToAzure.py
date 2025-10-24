from flask import Flask, render_template, request
import pyodbc
import pandas as pd

app = Flask(__name__)

# --- Azure SQL connection details ---
server = 'rental540.database.windows.net'
database = 'CloudRental540'
username = 'heema'
password = 'Preedi18'
driver = '{ODBC Driver 18 for SQL Server}'

# --- Homepage ---
@app.route('/', methods=['GET', 'POST'])
def home():
    total_revenue = None
    selected_location = None
    all_locations_revenue = None  # For total revenue across all locations

    try:
        # Connect to Azure SQL
        conn = pyodbc.connect(
            f'DRIVER={driver};SERVER={server};DATABASE={database};UID={username};PWD={password}'
        )

        # Get all locations for the dropdown
        locations_df = pd.read_sql("SELECT LocationID, LocationName FROM LOCATION ORDER BY LocationName", conn)
        locations = locations_df.to_dict(orient='records')

        if request.method == 'POST':
            selected_location = request.form.get('location')
            show_all = request.form.get('show_all')

            if show_all == "true":
                # Total revenue across all locations
                query = """
                    SELECT SUM(p.Amount) AS TotalRevenue
                    FROM RENTAL r
                    INNER JOIN PAYMENT p ON r.RentalID = p.RentalID
                    WHERE p.Status = 'Completed'
                """
                revenue_df = pd.read_sql(query, conn)
                if revenue_df.empty or revenue_df['TotalRevenue'].iloc[0] is None:
                    all_locations_revenue = "$0.00"
                else:
                    all_locations_revenue = "${:,.2f}".format(revenue_df['TotalRevenue'].iloc[0])
            else:
                # Total revenue for selected location
                query = """
                    SELECT SUM(p.Amount) AS TotalRevenue
                    FROM RENTAL r
                    INNER JOIN PAYMENT p ON r.RentalID = p.RentalID
                    INNER JOIN PICKUP_LOCATION pl ON r.RentalID = pl.RentalID
                    WHERE pl.LocationID = ? AND p.Status = 'Completed'
                """
                revenue_df = pd.read_sql(query, conn, params=[selected_location])
                if revenue_df.empty or revenue_df['TotalRevenue'].iloc[0] is None:
                    total_revenue = "$0.00"
                else:
                    total_revenue = "${:,.2f}".format(revenue_df['TotalRevenue'].iloc[0])

        conn.close()

    except Exception as e:
        return f"<h3 style='color:red;'>Error: {e}</h3>"

    return render_template('index.html',
                           locations=locations,
                           total_revenue=total_revenue,
                           selected_location=selected_location,
                           all_locations_revenue=all_locations_revenue)

if __name__ == '__main__':
    app.run(debug=True)

