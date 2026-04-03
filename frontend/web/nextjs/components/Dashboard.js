import WinnowingCore from './WinnowingCore';
import Governance from './Governance';
import Compliance from './Compliance';
import TechnicalHealth from './TechnicalHealth';
import Financial from './Financial';
import LessonsLearned from './LessonsLearned';

const Dashboard = () => {
  return (
    <div className="grid">
      <WinnowingCore />
      <Governance />
      <Compliance />
      <TechnicalHealth />
      <Financial />
      <LessonsLearned />
    </div>
  );
};

export default Dashboard;
