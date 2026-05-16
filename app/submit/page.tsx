export default function SubmitPage() {
  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">Submit Public Report</h1>
      <form className="card grid gap-3" method="post" action="/api/submissions">
        <input className="bg-slate-800 p-2 rounded" name="title" placeholder="Title" required />
        <input className="bg-slate-800 p-2 rounded" name="leader" placeholder="Leader Name" required />
        <input className="bg-slate-800 p-2 rounded" name="state" placeholder="State" required />
        <input className="bg-slate-800 p-2 rounded" name="source" placeholder="Source URL" required />
        <textarea className="bg-slate-800 p-2 rounded" name="description" placeholder="Description" required />
        <button className="bg-blue-600 hover:bg-blue-500 rounded p-2" type="submit">Submit</button>
      </form>
    </div>
  );
}
