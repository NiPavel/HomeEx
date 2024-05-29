import HidePage from "./HidePage";

const ReportShow = ({
  showSensors,
  setShowSensors,
  sensors,
  title,
  malfunctioningReport,
  aggregatedReport,
}) => {
  const hide = () => {
    setShowSensors(false);
  };

  return (
    <>
      <HidePage hide={showSensors} onClick={hide} />
      <div className="flex flex-col absolute z-10 bg-white h-100">
        <span className=" self-center">{title}</span>
        <hr />
        {sensors && (
          <ul className="overflow-scroll h-96 w-96">
            {sensors.map((sensor) => {
              return (
                <div key={sensor.id}>
                  <li>
                    Id: {sensor.id} | Face: {sensor.face}
                  </li>
                </div>
              );
            })}
          </ul>
        )}
        {malfunctioningReport && (
          <ul className="overflow-scroll h-96 w-96">
            {malfunctioningReport.map((report) => {
              return (
                <div key={report.id}>
                  <li>
                    Id: {report.sensor_id} | Face: {report.face} | Average
                    Temperature: {report.average_temperature}
                  </li>
                </div>
              );
            })}
          </ul>
        )}
        {aggregatedReport && (
          <ul className="overflow-scroll h-96 w-96">
            {aggregatedReport.map((report) => {
              return (
                <div key={report.id}>
                  <li>
                    Id: {report.id} | Face: {report.face} | Timestamp:{" "}
                    {report.hour} | Average Temperature:{" "}
                    {report.average_temperature}
                  </li>
                </div>
              );
            })}
          </ul>
        )}
      </div>
    </>
  );
};

export default ReportShow;
